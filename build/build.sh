#!/bin/bash

set -e

prompt_version() {
    read -p "Enter version for this release: " VERSION
    if [ -z "$VERSION" ]; then
        echo "Error: Version cannot be empty"
        exit 1
    fi
    echo "$VERSION" > ./VERSION
    echo "Version set to: $VERSION"
}

bundle() {
    rm -f ./build/mjml-bundled.js
    echo "Pre-bundling..."
    bun build ./build/mjml.mjs --outfile=./build/mjml-bundled.js --target=bun --format=iife --packages=bundle --minify
}

build_macos() {
    echo "Building macOS binaries..."
    for target in "darwin-x64" "darwin-arm64"
    do
        filename="mjml-$target"
        echo "Building: $target -> $filename"
        bun build --compile --minify --target="bun-$target" ./build/mjml-bundled.js --outfile=./bin/"$filename"
    done
}

build_linux_glibc() {
    echo "Building Linux (glibc) binaries via Docker..."
    for arch in "x64" "arm64"
    do
        filename="mjml-linux-$arch"
        echo "Building: linux-$arch (glibc) -> $filename"
        docker run --rm -v "$(pwd)":/host oven/bun:latest \
            sh -c "cd /tmp && bun build --compile --minify --target=bun-linux-$arch /host/build/mjml-bundled.js --outfile=$filename && cp $filename /host/bin/"
    done
}

build_linux_musl() {
    echo "Building Linux (musl) binaries via Docker..."
    for arch in "x64" "arm64"
    do
        filename="mjml-linux-$arch-musl"
        echo "Building: linux-$arch-musl -> $filename"
        docker run --rm -v "$(pwd)":/host oven/bun:alpine \
            sh -c "cd /tmp && bun build --compile --minify --target=bun-linux-$arch-musl /host/build/mjml-bundled.js --outfile=$filename && cp $filename /host/bin/"
    done
}

sign_macos() {
    echo "Signing macOS ARM64 binary..."
    local binary="./bin/mjml-darwin-arm64"
    codesign --remove-signature "$binary"
    codesign --sign "$MAC_DEVELOPER_KEY" --timestamp --options runtime --entitlements ./build/entitlements.plist "$binary"
    zip "$binary".zip "$binary"
    xcrun notarytool submit "$binary".zip --keychain-profile "$MACOS_NOTARIZATION" --wait
    rm "$binary".zip
}

upload() {
    echo "Uploading to DigitalOcean Spaces..."
    s3cmd --host https://nyc3.digitaloceanspaces.com \
        --host-bucket "defectivecode-packages.nyc3.digitaloceanspaces.com" \
        --acl-public \
        --access_key "$SPACES_KEY" \
        --secret_key "$SPACES_SECRET" \
        sync ./bin/mjml* s3://defectivecode-packages/packages/mjml/"$VERSION"/
}

cleanup() {
    echo "Cleaning up..."
    rm -f ./build/mjml-bundled.js
    find ./bin ! -name '.gitkeep' -type f -delete
    rm -f .*.bun-build
}

# Main
prompt_version
source .env
bun install
bundle
build_macos
build_linux_glibc
build_linux_musl
sign_macos
upload
cleanup
