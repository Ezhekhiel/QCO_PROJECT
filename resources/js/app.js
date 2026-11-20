
import "./bootstrap";

// Bootstrap
import * as bootstrap from "bootstrap";
import "bootstrap/dist/css/bootstrap.min.css";
import "bootstrap-icons/font/bootstrap-icons.min.css";

// OverlayScrollbars
import { OverlayScrollbars } from "overlayscrollbars";
import "overlayscrollbars/overlayscrollbars.css";

// SortableJS
import Sortable from "sortablejs";

// ApexCharts
import ApexCharts from "apexcharts";
import "apexcharts/dist/apexcharts.css";

// jsVectorMap CSS
import "jsvectormap/dist/css/jsvectormap.min.css";

// Font lokal
import "@fontsource/source-sans-3/400.css";
import "@fontsource/source-sans-3/600.css";
import "@fontsource/source-sans-3/700.css";

// Alpine.js
import Alpine from "alpinejs";

// jQuery dan Moment
import $ from "jquery";
import moment from "moment";

// Date Range Picker
import "daterangepicker";
import "daterangepicker/daterangepicker.css";

// Select2
import select2 from "select2";
import "select2/dist/css/select2.min.css";

// SweetAlert2
import Swal from "sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";

// Image Map Resizer
import imageMapResize from "image-map-resizer";

// Global Bootstrap
window.bootstrap = bootstrap;

// Global OverlayScrollbars
window.OverlayScrollbarsGlobal = { OverlayScrollbars };

// Global SortableJS
window.Sortable = Sortable;

// Global ApexCharts
window.ApexCharts = ApexCharts;

// Global Alpine
window.Alpine = Alpine;
Alpine.start();

// Global jQuery
window.$ = $;
window.jQuery = $;

// Global Moment
window.moment = moment;

// Global Select2
select2();

// Global SweetAlert2
window.Swal = Swal;

// Global Image Map Resizer
window.imageMapResize = imageMapResize;

document.addEventListener("DOMContentLoaded", function () {
    if (typeof window.imageMapResize === "function") {
        window.imageMapResize();
    }
});
