const fs = require('fs');
const path = require('path');

const walkDir = (dir, callback) => {
    fs.readdirSync(dir).forEach(f => {
        const dirPath = path.join(dir, f);
        const isDirectory = fs.statSync(dirPath).isDirectory();
        isDirectory ? walkDir(dirPath, callback) : callback(dirPath);
    });
};

const vueDir = path.join(__dirname, '../resources/js');

let filesModified = 0;

walkDir(vueDir, (filePath) => {
    if (filePath.endsWith('.vue')) {
        let content = fs.readFileSync(filePath, 'utf8');
        const originalContent = content;

        // Typography Scaling
        content = content.replace(/text-\[8px\]/g, 'text-xs')
                         .replace(/text-\[9px\]/g, 'text-xs')
                         .replace(/text-\[10px\]/g, 'text-xs')
                         .replace(/text-\[11px\]/g, 'text-sm')
                         .replace(/text-\[15px\]/g, 'text-sm');

        // Font Weights and Styles
        content = content.replace(/font-black uppercase tracking-\[0\.2em\]/g, 'font-medium text-muted-foreground uppercase tracking-tight')
                         .replace(/font-black uppercase tracking-widest/g, 'font-medium uppercase tracking-tight')
                         .replace(/font-black/g, 'font-bold')
                         .replace(/ tracking-widest/g, ' tracking-tight')
                         .replace(/ tracking-\[0\.2em\]/g, ' tracking-tight')
                         .replace(/ tracking-\[0\.3em\]/g, ' tracking-tight');

        // Aggressive Italics (we want to preserve ones that are intentionally used for quotes, but kill the standalone stylistic ones combined with opacity)
        content = content.replace(/italic opacity-80/g, 'text-muted-foreground')
                         .replace(/italic opacity-60/g, 'text-muted-foreground opacity-80')
                         .replace(/italic opacity-70/g, 'text-muted-foreground')
                         .replace(/ uppercase italic/g, '')
                         .replace(/ italic/g, '') // Actually, it's safer to just strip extraneous italics from utility strings
                         .replace(/class="([^"]*)italic([^"]*)"/g, 'class="$1$2"'); // specifically inside class strings

        // Aggressive Radii
        content = content.replace(/rounded-\[3rem\]/g, 'rounded-2xl')
                         .replace(/rounded-\[2\.5rem\]/g, 'rounded-xl')
                         .replace(/rounded-\[2rem\]/g, 'rounded-xl');

        // Neon Glows / Aggressive Shadows
        content = content.replace(/shadow-\[0_0_8px_rgba\([^\]]+\)\]/g, 'shadow-sm')
                         .replace(/shadow-xl shadow-blue-500\/10/g, 'shadow-sm')
                         .replace(/shadow-2xl/g, 'shadow-lg');

        // Terminology Replacements
        content = content.replace(/EXECUTING_PROTOCOL\.\.\./g, 'Processing...')
                         .replace(/CONFIRM_AUTHORIZATION/g, 'Confirm')
                         .replace(/ABORT_MISSION/g, 'Cancel')
                         .replace(/ADVANCING_REGISTRY\.\.\./g, 'Promoting...')
                         .replace(/INITIATE_PROMOTION_CYCLE/g, 'Promote Learners')
                         .replace(/CANCEL_SEQUENCE/g, 'Cancel');

        // Clean up double spaces in class names that might result from stripping
        content = content.replace(/class="([^"]+)"/g, (match, p1) => {
            return `class="${p1.replace(/\s+/g, ' ').trim()}"`;
        });

        if (content !== originalContent) {
            fs.writeFileSync(filePath, content, 'utf8');
            filesModified++;
        }
    }
});

console.log(`UI Standardization Complete. Modified ${filesModified} Vue files.`);
