<!-- Global Loader -->
<div id="global-loader" class="global-loader" style="display: none;">
    <div class="loader-overlay">
        <div class="loader-content">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="loading-text mt-2">Loading...</div>
        </div>
    </div>
</div>

<style>
.global-loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.loader-overlay {
    background-color: rgba(255, 255, 255, 0.9);
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.loader-content {
    text-align: center;
}

.loading-text {
    font-size: 14px;
    color: #333;
    margin-top: 10px;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .loader-overlay {
        background-color: rgba(0, 0, 0, 0.8);
    }
    
    .loading-text {
        color: #fff;
    }
}
</style>
