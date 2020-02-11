require('../sass/index.scss')

import {loadData, loadDoughnut, loadPages} from './analytics.js';


document.addEventListener('DOMContentLoaded', function(){
    $('#select-website').on('change', function(){
        loadData(true);
    })
    $('.select_start').on('change', function(e){
        loadDoughnut(e.target.dataset.name, true);
    })
    $('.select_start_pages').on('change', function(e){
        loadPages(true);
    })

    loadData(true);
}, false);

$(window).resize(function() {
    loadData();
})
