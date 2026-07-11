<script>
    (function () {
        var theme = 'light';

        try {
            theme = localStorage.getItem('btx-theme') === 'dark' ? 'dark' : 'light';
        } catch (error) {
            theme = 'light';
        }

        document.documentElement.dataset.btxTheme = theme;

        if (theme === 'dark') {
            var observer = new MutationObserver(function () {
                if (document.body) {
                    document.body.classList.add('dark-mode');
                    observer.disconnect();
                }
            });

            observer.observe(document.documentElement, { childList: true, subtree: true });
        }
    })();
</script>
