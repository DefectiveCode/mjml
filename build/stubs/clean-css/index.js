class CleanCSS {
    minify(source) {
        return { styles: source, errors: [], warnings: [] };
    }
}
module.exports = CleanCSS;
