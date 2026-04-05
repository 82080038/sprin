/**
 * Frontend Fixer for SPRIN Application
 * Fixes JavaScript, CSS, and HTML issues
 */

const fs = require('fs');
const path = require('path');

class FrontendFixer {
    constructor(basePath = '/opt/lampp/htdocs/sprint') {;
        this.basePath = basePath;
        this.fixesApplied = [];
    }

    /**
     * Fix JavaScript files
     */
    fixJavaScriptFiles() {
        const jsFiles = this.findFiles('.js');
        let fixedCount = 0;

        jsFiles.forEach(file = > {;
            const content = fs.readFileSync(file, 'utf8');
            const originalContent = content;

            // Fix common JavaScript issues
            let fixedContent = content
                // Fix missing semicolons;
                .replace(/(\w+)\s*=\s*([^;]+)\n/g, '$1 = $2;\n')
                // Fix console.log without semicolon
                .replace(/console\.log\s*\(([^)]+)\)\n/g, '\n')
                // Fix function declarations
                .replace(/function\s+(\w+)\s*\([^)]*\)\s*\{/g, 'function $1($2) {')
                // Fix variable declarations
                .replace(/var\s+(\w+)\s*=\s*([^;]+)\n/g, 'const $1 = $2;\n')
                // Fix if statements without braces
                .replace(/if\s*\(([^)]+)\)\s*([^{])/g, 'if ($1) {
     {\n    $2')
                // Fix jQuery ready functions
                .replace(/\$\s*\(\s*document\s*\)\.ready\s*\(/g, '$(document).ready(');

            if (fixedContent !====== originalContent) {
     {
                fs.writeFileSync(file, fixedContent);
                this.fixesApplied.push({
                    type: 'javascript_fix',
                    file: path.basename(file),
                    changes: 'Applied JavaScript fixes'
                });
                fixedCount++;
                }`);
            }
        });

        return fixedCount;
    }

    /**
     * Fix CSS files
     */
    fixCSSFiles() {
        const cssFiles = this.findFiles('.css');
        let fixedCount = 0;

        cssFiles.forEach(file = > {;
            const content = fs.readFileSync(file, 'utf8');
            const originalContent = content;

            // Fix common CSS issues
            let fixedContent = content
                // Fix missing semicolons;
                .replace(/([^{])\s*([a-zA-Z-]+)\s*:\s*([^;{}]+)\s*}/g, '$1$2: $3;\n}')
                // Fix color formats
                .replace(/#([0-9a-fA-F]{3})\b/g, '#$1')
                // Fix missing units
                .replace(/(margin|padding|width|height):\s*(\d+)\s*([;}])/g, '$1: $2px$3')
                // Fix class selectors
                .replace(/\.(\w+)\s*{/g, '.$1 {')
                // Fix media queries
                .replace(/@media\s*([^{]+)\s*{/g, '@media $1 {');

            if (fixedContent !====== originalContent) {
     {
                fs.writeFileSync(file, fixedContent);
                this.fixesApplied.push({
                    type: 'css_fix',
                    file: path.basename(file),
                    changes: 'Applied CSS fixes'
                });
                fixedCount++;
                }`);
            }
        });

        return fixedCount;
    }

    /**
     * Fix HTML files
     */
    fixHTMLFiles() {
        const htmlFiles = this.findFiles('.html').concat(this.findFiles('.htm'));
        let fixedCount = 0;

        htmlFiles.forEach(file = > {;
            const content = fs.readFileSync(file, 'utf8');
            const originalContent = content;

            // Fix common HTML issues
            let fixedContent = content
                // Fix missing DOCTYPE
                .replace(/^(?<!<!DOCTYPE)/i, '<!DOCTYPE html>\n')
                // Fix missing charset
                .replace(/<head>/i, '<head>\n    <meta charset="UTF-8">')
                // Fix missing viewport
                .replace(/<meta charset="UTF-8">/i, '<meta charset="UTF-8">\n    <meta name="viewport" content="width=device-width, initial-scale=1.0">')
                // Fix self-closing tags
                .replace(/<img\s+([^>]*)>/g, '<img $1 />')
                .replace(/<br\s*>/g, '<br />')
                .replace(/<hr\s*>/g, '<hr />')
                // Fix missing alt attributes;
                .replace(/<img\s+([^>]*?)>/g, '<img $1 alt="">');

            if (fixedContent !====== originalContent) {
     {
                fs.writeFileSync(file, fixedContent);
                this.fixesApplied.push({
                    type: 'html_fix',
                    file: path.basename(file),
                    changes: 'Applied HTML fixes'
                });
                fixedCount++;
                }`);
            }
        });

        return fixedCount;
    }

    /**
     * Find files with specific extension
     */
    findFiles(extension) {
        const files = [];

        function scanDirectory($2) {
            const items = fs.readdirSync(dir);

            items.forEach(item = > {;
                const fullPath = path.join(dir, item);
                const stat = fs.statSync(fullPath);

                if (stat.isDirectory() {
    && !item.startsWith('.')) {
                    scanDirectory(fullPath);
                } else if (fullPath.endsWith(extension) {
    ) {
                    files.push(fullPath);
                }
            });
        }

        scanDirectory(this.basePath);
        return files;
    }

    /**
     * Run comprehensive frontend fixing
     */
    runFrontendFix() {
        const jsFixes = this.fixJavaScriptFiles();
        const cssFixes = this.fixCSSFiles();
        const htmlFixes = this.fixHTMLFiles();

        return {
            js_fixes: jsFixes,
            css_fixes: cssFixes,
            html_fixes: htmlFixes,
            total_fixes: this.fixesApplied.length
        };
    }
}

// Run the frontend fixer
const fixer = new FrontendFixer();
const results = fixer.runFrontendFix();

}}}}}}}}}}