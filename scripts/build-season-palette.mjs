import fs from 'node:fs';
import path from 'node:path';
import postcss from 'postcss';
import valueParser from 'postcss-value-parser';

// Generate color-only overrides. Original portal styles remain byte-for-byte intact.
const themes = {
    halloween: ['#f0b368', '#de873d', '#ffdca4', '#100d09', '#1b1510', '#291f15', '#b9ab97', '#f6eddf', '#fff6e8', '#211508'],
    christmas: ['#9acfaf', '#579b7c', '#d2edd9', '#0b1511', '#12231c', '#1d3228', '#a3b9aa', '#edf5ef', '#f2fbf5', '#092015'],
    spring: ['#afd4bd', '#bfaddf', '#e0efe3', '#12121a', '#1d2027', '#293139', '#b4b6c7', '#f0f3f3', '#faf6ff', '#15221b'],
    summer: ['#f5c56b', '#66bdc4', '#ffedc1', '#0a1419', '#10212a', '#1b323b', '#a3bcc4', '#edf6f7', '#f2fbff', '#142027'],
    autumn: ['#dfaa76', '#b87748', '#f4d7b5', '#15100c', '#241b14', '#32261b', '#bba993', '#f5eadb', '#fff4e8', '#21160c'],
};
const names = ['accent', 'secondary', 'pale', 'bg', 'card', 'surface', 'muted', 'text', 'paper', 'on-accent'];
const rgb = hex => [1, 3, 5].map(i => Number.parseInt(hex.slice(i, i + 2), 16));
const prefix = 'html[data-season-palette]';

function tone(channels) {
    const [r, g, b] = channels.map(v => v / 255);
    const max = Math.max(r, g, b), min = Math.min(r, g, b), delta = max - min;
    const light = (max + min) / 2;
    const saturation = delta ? delta / (1 - Math.abs(2 * light - 1)) : 0;
    const hue = !delta ? 0 : ((max === r ? (g - b) / delta : max === g ? (b - r) / delta + 2 : (r - g) / delta + 4) * 60 + 360) % 360;
    if (hue < 260 || hue > 345 || !delta) return null;
    if (light > .96) return 'paper';
    if (light > .9 && saturation < .65) return 'text';
    if (light > .82) return 'pale';
    if (light < .08) return 'bg';
    if (light < .13) return 'card';
    if (light < .23) return 'surface';
    if (saturation < .26) return 'muted';
    if (hue < 312 || light < .5) return 'secondary';
    return 'accent';
}

function recolor(value) {
    const parsed = valueParser(value);
    parsed.walk(node => {
        if (node.type === 'function' && /^rgba?$/.test(node.value)) {
            const numbers = valueParser.stringify(node.nodes).split(',').map(v => v.trim());
            if (numbers.length < 3 || numbers.some(v => !Number.isFinite(Number(v)))) return false;
            const role = tone(numbers.slice(0, 3).map(Number));
            if (role) {
                node.type = 'word';
                node.value = `rgb(var(--season-${role}-rgb) / ${numbers[3] ?? '1'})`;
                delete node.nodes;
            }
            return false;
        }
        if (node.type === 'word' && /^#[\da-f]{6}$/i.test(node.value)) {
            const role = tone(rgb(node.value));
            if (role) node.value = `var(--season-${role})`;
        }
    });
    return parsed.toString();
}

function scope(selector) {
    if (/^html\b/.test(selector)) return selector.replace(/^html\b/, prefix);
    if (/^\[data-bs-theme/.test(selector)) return `${prefix}${selector}`;
    return `${prefix} ${selector}`;
}

function strongFill(value) {
    return /^var\(--rose(?:-deep)?\)$/.test(value)
        || (/linear-gradient/.test(value) && /var\(--(?:rose(?:-deep)?|season-accent|season-secondary)\)/.test(recolor(value)))
        || (/^#[\da-f]{6}$/i.test(value) && tone(rgb(value)) === 'accent')
        || [...value.matchAll(/rgba\(([^)]+)\)/g)].some(match => {
            const parts = match[1].split(',').map(Number);
            return parts.length === 4 && parts[3] >= .75 && ['accent', 'secondary'].includes(tone(parts.slice(0, 3)));
        });
}

function overrides(container) {
    const result = postcss.root();
    for (const node of container.nodes ?? []) {
        if (node.type === 'atrule' && ['media', 'supports', 'layer', 'container'].includes(node.name)) {
            const children = overrides(node);
            if (children.nodes.length) result.append(node.clone({ nodes: children.nodes }));
        } else if (node.type === 'rule' && !node.selector.includes(':root')) {
            // Semantic warnings, status indicators and destructive actions keep their colors.
            if (/(?:danger|error|warning|success|is-online|is-offline)/i.test(node.selector)) continue;
            const rule = postcss.rule({ selector: node.selectors.map(scope).join(',') });
            let filled = false;
            for (const decl of node.nodes ?? []) {
                if (decl.type !== 'decl') continue;
                if (/^background(?:-color)?$/.test(decl.prop) && strongFill(decl.value)) filled = true;
                const value = recolor(decl.value);
                if (value !== decl.value) rule.append(decl.clone({ value }));
            }
            if (filled) rule.append({ prop: 'color', value: 'var(--season-on-accent)', important: node.nodes.some(decl => decl.prop === 'color' && decl.important) });
            if (rule.nodes.length) result.append(rule);
        }
    }
    return result;
}

function bladeStyles(directory) {
    const result = [];
    for (const entry of fs.readdirSync(directory, { withFileTypes: true })) {
        const file = path.join(directory, entry.name);
        if (entry.isDirectory()) result.push(...bladeStyles(file));
        else if (file.endsWith('.blade.php')) {
            const content = fs.readFileSync(file, 'utf8');
            for (const match of content.matchAll(/<style[^>]*>([\s\S]*?)<\/style>/g)) result.push([file, match[1]]);
        }
    }
    return result;
}

const sources = ['style.css', 'episodios.css', 'dialogs.css', 'home-grid.css'].map(name => {
    const file = `public/assets/css/${name}`;
    return [file, fs.readFileSync(file, 'utf8')];
});
for (const directory of ['components', 'catalog', 'profile', 'community', 'forums', 'questions']) {
    sources.push(...bladeStyles(`resources/views/${directory}`));
}
const root = postcss.root();
for (const [theme, colors] of Object.entries(themes)) {
    const rule = postcss.rule({ selector: `html[data-season-palette="${theme}"]` });
    colors.forEach((value, i) => {
        rule.append({ prop: `--season-${names[i]}`, value });
        rule.append({ prop: `--season-${names[i]}-rgb`, value: rgb(value).join(' ') });
    });
    for (const [prop, value] of Object.entries({
        '--rose': 'var(--season-accent)', '--rose-deep': 'var(--season-secondary)',
        '--rose-pale': 'var(--season-pale)', '--rose-glow': 'rgb(var(--season-accent-rgb) / .18)',
        '--dark': 'var(--season-bg)', '--dark-card': 'var(--season-card)', '--dark-surface': 'var(--season-surface)',
        '--muted': 'var(--season-muted)', '--text': 'var(--season-text)',
        '--bs-primary': 'var(--season-accent)', '--bs-primary-rgb': rgb(colors[0]).join(','),
        '--bs-link-color-rgb': rgb(colors[0]).join(','), '--bs-link-hover-color-rgb': rgb(colors[2]).join(','),
        '--bs-link-color': 'var(--season-accent)', '--bs-link-hover-color': 'var(--season-pale)',
    })) rule.append({ prop, value });
    root.append(rule);
}
for (const [from, css] of sources) root.append(overrides(postcss.parse(css, { from })).nodes);

// Compact the generated file without changing selector specificity or media conditions.
root.walk(node => {
    node.raws.before = '';
    if (node.type === 'decl') node.raws.between = ':';
    else node.raws.between = '';
    node.raws.after = '';
});
root.raws.after = '';
const finish = `
html[data-season-palette] :is(.btn-rose,.profile-btn-primary,.portal-auth-submit,.forum-search button,.catalog-clear-filters,.submission-page .btn-primary){background:linear-gradient(135deg,var(--season-accent),var(--season-secondary));border-color:var(--season-accent);color:var(--season-on-accent)}
html[data-season-palette] :is(.btn-rose,.profile-btn-primary,.portal-auth-submit,.forum-search button,.catalog-clear-filters):is(:hover,:focus-visible){background:linear-gradient(135deg,var(--season-pale),var(--season-accent));color:var(--season-on-accent)}
html[data-season-palette] .mundo-dialog:not([data-kind='danger']) .mundo-dialog-confirm{background:linear-gradient(135deg,var(--season-accent),var(--season-secondary));color:var(--season-on-accent)}
html[data-season-palette] .portal-auth-tabs button[aria-selected='true']{color:var(--season-on-accent)}
html[data-season-palette] .btn-primary{--bs-btn-color:var(--season-on-accent);--bs-btn-bg:var(--season-accent);--bs-btn-border-color:var(--season-accent);--bs-btn-focus-shadow-rgb:var(--bs-primary-rgb);--bs-btn-hover-color:var(--season-on-accent);--bs-btn-hover-bg:var(--season-pale);--bs-btn-hover-border-color:var(--season-pale);--bs-btn-active-color:var(--season-on-accent);--bs-btn-active-bg:var(--season-secondary);--bs-btn-active-border-color:var(--season-secondary);--bs-btn-disabled-color:var(--season-on-accent);--bs-btn-disabled-bg:var(--season-accent);--bs-btn-disabled-border-color:var(--season-accent)}
html[data-season-palette] .btn-outline-primary{--bs-btn-color:var(--season-accent);--bs-btn-border-color:var(--season-accent);--bs-btn-hover-color:var(--season-on-accent);--bs-btn-hover-bg:var(--season-accent);--bs-btn-hover-border-color:var(--season-accent);--bs-btn-active-color:var(--season-on-accent);--bs-btn-active-bg:var(--season-accent);--bs-btn-active-border-color:var(--season-accent)}
html[data-season-palette] :is(.btn-rose,.profile-btn-primary,.portal-auth-submit,.ep-play-icon,.cat-play,.continue-play) svg[fill]:not([fill='none']){fill:currentColor}
html[data-season-palette] :is(.btn-rose,.ep-play-icon,.cat-play,.continue-play) svg [fill='#fff']{fill:currentColor}
html[data-season-palette] .portal-preference-switch.is-active{background:var(--season-accent)}
html[data-season-palette] .portal-preference-switch.is-active span{background:var(--season-on-accent)}
html[data-season-palette] .pagination .page-item.active .page-link{color:var(--season-on-accent)}
html[data-season-palette] :is([style*='border-color:rgba(244,63,142'],[style*='border-color: rgba(244, 63, 142']){border-color:rgb(var(--season-accent-rgb)/.22)!important}
html[data-season-palette] .hero.hero--halloween{background:var(--season-bg)}
html[data-season-palette] .hero.hero--halloween .hero-overlay{background:linear-gradient(to top,var(--season-bg),rgb(var(--season-bg-rgb)/.12) 28%,transparent 56%),linear-gradient(90deg,rgb(var(--season-bg-rgb)/.25),transparent 65%)}
@media(max-width:768px){html[data-season-palette] .hero.hero--halloween .hero-overlay{background:linear-gradient(to top,var(--season-bg),rgb(var(--season-bg-rgb)/.18) 34%,transparent 65%)}}
`;
process.stdout.write('/* Generated color overrides. Rebuild with scripts/build-season-palette.mjs. */\n' + root.toString() + finish);
