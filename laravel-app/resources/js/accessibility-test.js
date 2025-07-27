/**
 * アクセシビリティテスト用のユーティリティ関数
 */

// コントラスト比を計算する関数
function calculateContrastRatio(color1, color2) {
    const getLuminance = (color) => {
        const rgb = color.match(/\d+/g);
        const [r, g, b] = rgb.map(c => {
            c = c / 255;
            return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
        });
        return 0.2126 * r + 0.7152 * g + 0.0722 * b;
    };

    const lum1 = getLuminance(color1);
    const lum2 = getLuminance(color2);
    const brightest = Math.max(lum1, lum2);
    const darkest = Math.min(lum1, lum2);
    
    return (brightest + 0.05) / (darkest + 0.05);
}

// カラーパレットのコントラスト比をチェック
function checkColorContrast() {
    const colors = {
        'lib-primary': '#3D7CA3',
        'lib-secondary': '#669C6F', 
        'lib-accent': '#E3595B',
        'white': '#FFFFFF',
        'lib-primary-900': '#1A2E3A',
        'lib-secondary-900': '#2A3A2F',
        'lib-accent-900': '#7A2B2D'
    };

    const results = [];
    
    // 主要な色の組み合わせをテスト
    const combinations = [
        ['lib-primary', 'white'],
        ['lib-secondary', 'white'],
        ['lib-accent', 'white'],
        ['lib-primary-900', 'lib-primary-light'],
        ['lib-secondary-900', 'lib-secondary-light'],
        ['lib-accent-900', 'lib-accent-light']
    ];

    combinations.forEach(([color1, color2]) => {
        if (colors[color1] && colors[color2]) {
            const ratio = calculateContrastRatio(colors[color1], colors[color2]);
            const passAA = ratio >= 4.5;
            const passAAA = ratio >= 7;
            
            results.push({
                combination: `${color1} on ${color2}`,
                ratio: ratio.toFixed(2),
                passAA,
                passAAA
            });
        }
    });

    return results;
}

// フォーカス可能な要素をチェック
function checkFocusableElements() {
    const focusableSelectors = [
        'a[href]',
        'button:not([disabled])',
        'input:not([disabled])',
        'select:not([disabled])',
        'textarea:not([disabled])',
        '[tabindex]:not([tabindex="-1"])'
    ];

    const focusableElements = document.querySelectorAll(focusableSelectors.join(','));
    const results = [];

    focusableElements.forEach((element, index) => {
        const hasVisibleFocus = window.getComputedStyle(element, ':focus').outline !== 'none' ||
                               window.getComputedStyle(element, ':focus').boxShadow !== 'none';
        
        results.push({
            element: element.tagName.toLowerCase(),
            hasAriaLabel: element.hasAttribute('aria-label'),
            hasVisibleFocus,
            tabIndex: element.tabIndex
        });
    });

    return results;
}

// ARIA属性をチェック
function checkAriaAttributes() {
    const elementsWithAria = document.querySelectorAll('[role], [aria-label], [aria-describedby], [aria-live]');
    const results = [];

    elementsWithAria.forEach(element => {
        results.push({
            element: element.tagName.toLowerCase(),
            role: element.getAttribute('role'),
            ariaLabel: element.getAttribute('aria-label'),
            ariaDescribedby: element.getAttribute('aria-describedby'),
            ariaLive: element.getAttribute('aria-live')
        });
    });

    return results;
}

// アクセシビリティレポートを生成
function generateAccessibilityReport() {
    const report = {
        timestamp: new Date().toISOString(),
        colorContrast: checkColorContrast(),
        focusableElements: checkFocusableElements(),
        ariaAttributes: checkAriaAttributes()
    };

    console.group('🔍 アクセシビリティレポート');
    console.log('生成時刻:', report.timestamp);
    
    console.group('🎨 カラーコントラスト');
    report.colorContrast.forEach(result => {
        const status = result.passAA ? '✅' : '❌';
        console.log(`${status} ${result.combination}: ${result.ratio}:1 (AA: ${result.passAA ? 'PASS' : 'FAIL'})`);
    });
    console.groupEnd();
    
    console.group('🎯 フォーカス可能要素');
    const focusIssues = report.focusableElements.filter(el => !el.hasVisibleFocus);
    console.log(`総数: ${report.focusableElements.length}, フォーカス表示問題: ${focusIssues.length}`);
    console.groupEnd();
    
    console.group('🏷️ ARIA属性');
    console.log(`ARIA属性を持つ要素: ${report.ariaAttributes.length}`);
    console.groupEnd();
    
    console.groupEnd();
    
    return report;
}

// ページ読み込み時にレポートを生成（開発環境のみ）
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(generateAccessibilityReport, 1000);
    });
}

// グローバルに公開
window.accessibilityTest = {
    generateReport: generateAccessibilityReport,
    checkColorContrast,
    checkFocusableElements,
    checkAriaAttributes
};