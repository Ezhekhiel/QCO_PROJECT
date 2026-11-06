<script src="https://cdnjs.cloudflare.com/ajax/libs/image-map-resizer/1.0.10/js/imageMapResizer.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    imageMapResize();
</script>

<!-- ChartJS -->
<script>
    function fullscreen(btn, status) {

        let sidebar = document.getElementById("display_sidebar");
        let header = document.getElementById("display_header");
        let breadcrumb = document.getElementById("display_breadcrumb");
        let content = document.getElementsByClassName("app-content")[0];

        let icon = btn.querySelector("i"); // 🔥 ambil icon

        if (status == true) {

            [sidebar, header, breadcrumb].forEach(el => {
                el.classList.add("smooth-hide");
                el.classList.remove("smooth-show");

                setTimeout(() => {
                    el.style.display = "none";
                }, 150); // kasih 300 biar animasi keliatan
            });

            content.classList.add("mt-4");

            btn.setAttribute("onclick", "fullscreen(this, false)");

            // 🔥 ubah icon
            icon.classList.remove("bi-arrows-fullscreen");
            icon.classList.add("bi-fullscreen-exit");

        } else {

            [sidebar, header, breadcrumb].forEach(el => {
                el.style.display = "block";

                setTimeout(() => {
                    el.classList.remove("smooth-hide");
                    el.classList.add("smooth-show");
                }, 10);
            });

            content.classList.remove("mt-4");

            btn.setAttribute("onclick", "fullscreen(this, true)");

            // 🔥 ubah icon balik
            icon.classList.remove("bi-fullscreen-exit");
            icon.classList.add("bi-arrows-fullscreen");
        }
    }
    // Deteksi jika fokus ke input (keyboard muncul) di tablet/mobile
    const inputs = document.querySelectorAll('input, textarea, select');
    const body = document.body;

    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            // Jika layar berukuran tablet/mobile, kunci class sidebar saat ini
            if (window.innerWidth < 992) {
                body.classList.add('keyboard-open');
            }
        });

        input.addEventListener('blur', () => {
            body.classList.remove('keyboard-open');
        });
    });
</script>
