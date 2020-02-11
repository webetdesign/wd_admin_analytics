require('../sass/index.scss')

import {loadData, loadDoughnut} from './analytics.js';


document.addEventListener('DOMContentLoaded', function(){
    $('#select-website').on('change', function(){
        loadData(true);
    })
    $('.select_start').on('change', function(e){
        loadDoughnut(e.target.dataset.name, true);
    })
    loadData(true);
}, false);

$(window).resize(function() {
    loadData();
})
