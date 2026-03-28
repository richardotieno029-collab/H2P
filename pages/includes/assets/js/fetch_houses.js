// fetch_houses.js

document.addEventListener("DOMContentLoaded", () => {

    const form = document.querySelector('.filters');
    const container = document.getElementById('housesContainer');

    /* =========================
       LOAD HOUSES
    ========================= */
    function loadHouses(){

        const formData = new FormData(form);
        const params = new URLSearchParams(formData).toString();

        container.innerHTML = `
        <div class="loading">
            <div class="spinner"></div>
            <p>Loading houses...</p>
        </div>
        `;

        fetch("fetch_houses.php?" + params)
        .then(res => res.text())
        .then(data => {

            container.innerHTML = data;

            attachFavEvents();

            /* RESTORE SCROLL */
            const savedScroll = sessionStorage.getItem("scrollPosition");
            if(savedScroll){
                window.scrollTo({
                    top: parseInt(savedScroll),
                    behavior: "auto"
                });
                sessionStorage.removeItem("scrollPosition");
            }

        });
    }

    /* =========================
       INITIAL LOAD
    ========================= */
    loadHouses();

    /* =========================
       FILTER CHANGE
    ========================= */
    form.addEventListener('change', () => {
        sessionStorage.removeItem("scrollPosition");
        loadHouses();
    });

    /* =========================
       SAVE SCROLL
    ========================= */
    window.addEventListener("beforeunload", () => {
        sessionStorage.setItem("scrollPosition", window.scrollY);
    });

    /* =========================
       AUTO REFRESH
    ========================= */
    let autoRefresh = true;

    ['scroll', 'click', 'keydown', 'mousemove', 'touchstart']
    .forEach(event => {
        document.addEventListener(event, () => {
            autoRefresh = false;
            setTimeout(() => autoRefresh = true, 60000);
        });
    });

    setInterval(() => {
        if(autoRefresh){
            sessionStorage.setItem("scrollPosition", window.scrollY);
            loadHouses();
        }
    }, 30000);

    /* =========================
       FAVOURITES
    ========================= */
    function attachFavEvents(){

        document.querySelectorAll('.fav-btn').forEach(button => {

            button.addEventListener('click', function(e){

                e.preventDefault();
                e.stopPropagation();

                const houseId = this.dataset.houseId;
                const btn = this;

                fetch('../favourites/toggle_favourite.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'house_id=' + houseId
                })
                .then(res => res.json())
                .then(data => {

                    if(data.status === 'added'){
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }

                });

            });

        });

    }

});