import "./bootstrap";

import Alpine from "alpinejs";

window.Alpine = Alpine;

Alpine.start();

import $ from "jquery";
window.$ = $;
window.jQuery = $;

import moment from "moment";
window.moment = moment;

import "daterangepicker";
import "daterangepicker/daterangepicker.css";

import select2 from "select2";
import "select2/dist/css/select2.min.css";

select2();
