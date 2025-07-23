#!/bin/bash

source .env
targets=("darwin-x64" "darwin-arm64" "linuxstatic-x64" "linuxstatic-arm64")

for target in "${targets[@]}"
do
  case "$target" in
      linuxstatic-x64)    filename="mjml-linux-x64"    ;;
      linuxstatic-arm64)  filename="mjml-linux-arm64"  ;;
      *)                  filename="mjml-$target"    ;;
  esac
  echo "Building: $target ($filename)"
  npx pkg --target=node20-"$target" ./build/mjml.js --compress=Brotli --output=./bin/"$filename"
done

echo "Signing MacOS ARM64 binary."
macos_arm64_binary="./bin/mjml-darwin-arm64"
codesign --remove-signature "$macos_arm64_binary"
codesign --sign "$MAC_DEVELOPER_KEY" --timestamp --options runtime --entitlements ./build/entitlements.plist "$macos_arm64_binary"
zip "$macos_arm64_binary".zip "$macos_arm64_binary"
xcrun notarytool submit "$macos_arm64_binary".zip --keychain-profile "$MACOS_NOTARIZATION" --wait
rm "$macos_arm64_binary".zip

echo "Uploading to DigitalOcean Spaces"
s3cmd --host https://nyc3.digitaloceanspaces.com --host-bucket "defectivecode-packages.nyc3.digitaloceanspaces.com" --acl-public --access_key "$SPACES_KEY" --secret_key "$SPACES_SECRET" sync ./bin/mjml* s3://defectivecode-packages/packages/mjml/"$VERSION"/

echo "Cleaning up"
find ./bin ! -name '.gitkeep' -type f -delete
