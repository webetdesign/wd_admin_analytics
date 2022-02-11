require('../sass/index.scss')

import {loadNewsletter, loadData, loadGraph, loadPages, loadUsers, loadMap} from './analytics.js';


document.addEventListener('DOMContentLoaded', function(){
    $('#select-website').on('change', function(){
        loadData(true);
        loadNewsletter(true);
    })
    $('.select_start').on('change', function(e){
        let name = e.target.dataset.name;
        let type = e.target.dataset.type;
        let label = e.target.dataset.label;
        if (name == 'countries'){
            loadMap(name, true);
        }else{
            loadGraph(name, true, type, label);
        }
    })
    $('#select_newsletter').on('change', function(e){
        let name = e.target.dataset.name;
        let type = e.target.dataset.type;
        let label = e.target.dataset.label;
        loadGraph(name, true, type, label);
    })
    
    $('.select_start_pages').on('change', function(e){
        loadPages(true);
    })

    loadData(true);
}, false);

$(window).resize(function() {
    loadData();
})
