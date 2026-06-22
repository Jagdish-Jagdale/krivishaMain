// Dashboard Chart Display Fix
// This script ensures all chart labels, ticks, and values are properly displayed

// Set default Chart.js options for better visibility
if (typeof Chart !== 'undefined') {
    Chart.defaults.color = '#2c343d';
    Chart.defaults.font.family = "'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";
    Chart.defaults.font.size = 11;
    
    // Default scale configuration
    Chart.defaults.scale.ticks.color = '#6e7a89';
    Chart.defaults.scale.grid.color = 'rgba(0, 0, 0, 0.05)';
    
    // Ensure ticks are always displayed
    Chart.defaults.scale.ticks.display = true;
    Chart.defaults.scale.ticks.autoSkip = true;
    Chart.defaults.scale.ticks.maxTicksLimit = 8;
    
    // Default plugins configuration
    Chart.defaults.plugins.legend.labels.color = '#2c343d';
    Chart.defaults.plugins.legend.labels.font = {
        size: 11,
        family: "'Poppins', sans-serif"
    };
}

// Helper function to ensure proper chart configuration
function ensureChartConfig(config) {
    if (!config.options) {
        config.options = {};
    }
    
    if (!config.options.scales) {
        config.options.scales = {};
    }
    
    // Ensure y-axis is properly configured
    if (config.options.scales.y) {
        if (!config.options.scales.y.ticks) {
            config.options.scales.y.ticks = {};
        }
        config.options.scales.y.ticks.display = true;
        config.options.scales.y.ticks.color = '#6e7a89';
        config.options.scales.y.display = true;
    }
    
    // Ensure x-axis is properly configured
    if (config.options.scales.x) {
        if (!config.options.scales.x.ticks) {
            config.options.scales.x.ticks = {};
        }
        config.options.scales.x.ticks.display = true;
        config.options.scales.x.ticks.color = '#6e7a89';
        config.options.scales.x.display = true;
    }
    
    return config;
}

// Export for use in other scripts
if (typeof window !== 'undefined') {
    window.ensureChartConfig = ensureChartConfig;
}
