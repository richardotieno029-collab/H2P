function startAutoRefresh(seconds = 10) {

    let timer;

    function resetTimer() {
        clearTimeout(timer);
        timer = setTimeout(() => {
            location.reload();
        }, seconds * 1000);
    }

    // reset on user activity (prevents annoying reloads while interacting)
    ['click', 'mousemove', 'keydown', 'scroll', 'touchstart']
    .forEach(event => {
        document.addEventListener(event, resetTimer);
    });

    // start initial timer
    resetTimer();
}