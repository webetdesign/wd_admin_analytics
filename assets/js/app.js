require('../sass/index.scss')

import {loadData, loadDoughnut, loadPages, loadUsers, loadMap} from './analytics.js';


document.addEventListener('DOMContentLoaded', function(){
    $('#select-website').on('change', function(){
        loadData(true);
    })
    $('.select_start').on('change', function(e){
        let name = e.target.dataset.name;
        if (name == 'countries'){
            loadMap(name, true);
        }else{
            loadDoughnut(name, true);
        }
    })
    $('.select_start_pages').on('change', function(e){
        loadPages(true);
    })

    loadData(true);
}, false);

$(window).resize(function() {
    loadData();
})
