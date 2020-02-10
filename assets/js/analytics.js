import Chart from "chart.js"
// import './gapi.js';

document.addEventListener('DOMContentLoaded', function(){
    $('#select-website').on('change', function(){
        loadData();
    })
    loadData();
}, false);

$(window).resize(function() {
    loadData();
})

function loadData(){
    var colors = null;
    var site_id = document.getElementById('select-website').selectedOptions[0].value;

    if ( document.getElementById("colors")){
        colors = document.getElementById("colors").dataset.colors;
        colors = JSON.parse(colors);
    }

    if (document.getElementById("browsers-container") != null){
        var browsers = document.getElementById("data-browsers"  + '-' + site_id).dataset.values;
        renderDoughnut(JSON.parse(browsers), colors, "browsers");
    }

    if (document.getElementById('week-container') != null){

        var week_colors = {
            0 : null,
            1 : null
        };

        if ( document.getElementById("week_colors") != null){
            week_colors = document.getElementById("week_colors").dataset.weekcolors;
            week_colors = JSON.parse(week_colors);
        }

        var weeks = document.getElementById("data-userWeek" + '-' + site_id).dataset.values;

        renderWeekOverWeekChart(JSON.parse(weeks), week_colors);
    }

    if (document.getElementById('year-container') != null){

        var year_colors = {
            0 : null,
            1 : null
        };

        if ( document.getElementById("year_colors") != null){
            year_colors = document.getElementById("year_colors").dataset.yearcolors;
            year_colors = JSON.parse(year_colors);
        }

        var years = document.getElementById("data-userYear" + '-' + site_id).dataset.values;

        renderYearOverYearChart(JSON.parse(years), year_colors);
    }

    if (document.getElementById("sources-container") != null){
        var sources = document.getElementById("data-sources" + '-' + site_id).dataset.values;
        renderDoughnut(JSON.parse(sources), colors, "sources");
    }

    if (document.getElementById("devices-container") != null){
        var devices = document.getElementById("data-devices" + '-' + site_id).dataset.values;
        renderDoughnut(JSON.parse(devices), colors, "devices");
    }

    if (document.getElementById("pages-container") != null){
        var pages = document.getElementById("data-pages" + '-' + site_id).dataset.values;
        renderPages(JSON.parse(pages));
    }

    if (document.getElementById("countries-container") != null){
        var countries = document.getElementById("data-countries" + '-' + site_id).dataset.values;
        var map = document.getElementById("map_key").dataset.mapkey;
        var map_color = document.getElementById("map_color").dataset.mapcolor;

        renderCountries(JSON.parse(countries), map_color    , map);
    }

    if (document.getElementById("users-container") != null){
        var users_color = document.getElementById("users_color").dataset.userscolor;
        var users = document.getElementById("data-users" + '-' + site_id).dataset.values;
        renderUsers(JSON.parse(users), users_color, "users");
    }
}

function renderDoughnut(response, colors, name) {

    if (response.labels.length === 0){
        $('#' + name + '-container')[0].innerHTML = "<h4> Vous n'avez pas de données pour ce type d'analytics </h4>";
        return;
    };

    var data = [];
    var colors_chart = [];
    var values = [];
    var labels = [];

    for (let i = 0; i < response.labels.length; i++) {
        values.push(response.values[i]);
        labels.push(response.labels[i]);
        colors_chart.push(colors[i]);
    }

    data['datasets'] =  [];
    data['datasets'].push({
        "data": values,
        "backgroundColor" : colors_chart
    });

    data['labels'] =  labels;
    data['diff'] =  response.diff;
    data['percents'] =  response.percents;
    data['total'] =  response.total[0];


    var chart = new Chart(makeCanvas(name + '-container'), {
        type: 'doughnut',
        data: data,
        options: {
            cutoutPercentage: 70,
            legend: false,
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var label = data.labels[tooltipItem.index][0];

                        if (label) {
                            label += ': ';
                        }
                        label += data.datasets[0].data[tooltipItem.index]
                        return label;
                    }
                }
            },
            legendCallback: function(chart) {
                var legendHtml = [];
                var item = chart.data.datasets[0];
                legendHtml.push('<table style="margin-bottom: 5px; margin-top: 30px"><tr>')
                for (var i=0; i < item.data.length; i++) {
                    legendHtml.push('<td class="chart-legend" style="color:' + item.backgroundColor[i] +'"><i class="' + chart.data.labels[i][1] +'"></i></td>');
                }
                legendHtml.push('</tr>')

                legendHtml.push('<tr>')
                for (var i=0; i < item.data.length; i++) {
                    legendHtml.push('<td class="chart-legend-label-text">' + chart.data.labels[i][0] +'</td>');
                }
                legendHtml.push('</tr>')

                legendHtml.push('<tr>')
                for (var i=0; i < item.data.length; i++) {
                    var prct = parseFloat(chart.data.percents[i]).toFixed(2)
                    legendHtml.push('<td class="chart-legend-label-text">' + prct +' %</td>');
                }
                legendHtml.push('</tr>')

                legendHtml.push('<tr>')
                for (var i=0; i < item.data.length; i++) {
                    var prct = parseFloat(chart.data.diff[i]).toFixed(2)
                    var css = prct >= 0 ? 'text-success' : 'text-danger';
                    legendHtml.push('<td class="chart-legend-label-diff ' + css + ' ">' + prct +' %</td>');
                }
                legendHtml.push('</tr>')

                legendHtml.push('</table>')
                return legendHtml.join("");
            }
        },
    });

    $('#' + name + '-legend').html(chart.generateLegend());
}

function renderWeekOverWeekChart(data, colors) {

    var values = {
        labels : data.labels,
        datasets : [
            {
                label: 'Semaine dernière',
                borderColor : colors[0],
                pointColor : colors[0],
                backgroundColor: "rgb(255, 255, 255, 0)",
                borderDash: [10, 8],
                pointStrokeColor : '#fff',
                data :  data.values.last_week
            },
            {
                label: 'Cette semaine',
                borderColor : colors[1],
                backgroundColor: "rgb(255, 255, 255, 0)",
                pointColor : colors[1],
                pointStrokeColor : '#fff',
                data : data.values.this_week
            }
        ]
    };

    var  options = {};
    new Chart(makeCanvas('week-container'), {
        type: 'line',
        data: values,
        options: options
    });
}

function renderYearOverYearChart(data, colors) {

    var values = {
        labels : data.labels,
        datasets : [
            {
                label: 'Année dernière',
                backgroundColor : colors[0],
                data : data.values.last_year
            },
            {
                label: 'Cette année',
                backgroundColor : colors[1],
                data : data.values.this_year
            }
        ]
    };

    var  options = {};
    new Chart(makeCanvas('year-container'), {
        type: 'bar',
        data: values,
        options: options
    });
}

function renderCountries(data, color, mapKey){
    google.charts.load('current', {
        'packages':['geochart'],
        'mapsApiKey': mapKey,
    });

    setTimeout(function() {
        google.charts.setOnLoadCallback(drawMap(data, color));
    }, 2000)

}

function renderUsers(data, color){
    var visits = data["values"];
    var max = data["max"];
    $("#users-container")[0].innerHTML = "";

    $.each(visits, function(i, row) {
        var id = "row-" + i ;
        $("#users-container").append('<div class="row " id="'+id+'">\n' +
            '\n' +
            '</div>'
        )
        $.each(row, function(j, value) {
            var colorDiv = getColorUser(max, value, color);
            $("#"+id).append('<div ' +
                'class="col-xs-1 m-1 " ' +
                'style="background-color: '+ colorDiv +'; height: 12px; border: 2px solid white" ' +
                'rel=\'tooltip\' data-original-title=\'' +
                '<span style=" color: #A6ACAF;">' + getDay(j) + ' ' + i + 'h' + '</span>' +
                '<br>' +
                '<span style="font-size: 1.6rem; color: white;">'+ value +'</span>' +
                '<br>' +
                '<span style=" color: #A6ACAF;">' + (value < 2 ? 'Utilisateur' : 'Utilisateurs') +'</span>' +
                '\'' +
                '>\n</div>'
            );
        })

    })

    $("#users-container").append('<div class="row" id="row-date">\n' +
        '\n' +
        '</div>'
    )
    for (var i = 0; i < 7; i++) {
        $("#row-date").append('<div class="col-xs-1 m-1 text-center" style=" height: 10px; border: 1px solid inherit; left: -5px;  font-size: 1.2rem; color: #A6ACAF;" >'+ getDay(i)  + '</div>');
    }

    $("[rel=tooltip]").tooltip({html:true});
}

function renderPages(data){
    var container = $("#pages-container");

    var html = '<table class="pages-table">';

    html += '<tr><td></td><td>Vues</td></tr>'

    for (let i = 0; i < data.labels.length; i++) {
        html += '<tr>' +
            '<td>' + data.labels[i] + '</td>' +
            '<td>' + data.values[i] + '</td>' +
            '</tr>';
    }

    html += '</table>';

    container.html(html);

}

function getColorUser(max, value, color){
    if (value === 0) return "#dfdfdf";
    var prct = value / max;
    return color.substring(0, 17) + ", " + (prct * 2) + ")";

}

function getDay(day) {
    switch (day) {
        case 0:
        case 'Monday':
            return 'lun.';
        case 1:
        case 'Tuesday':
            return 'mar.';
        case 2:
        case 'Wednesday':
            return 'mer.';
        case 3:
        case 'Thursday':
            return 'jeu.';
        case 4:
        case 'Friday':
            return 'ven.';
        case 5:
        case 'Saturday':
            return 'sam.';
        case 6:
        case 'Sunday':
            return 'dim.';
        default:
            return day;
    }
}

function drawMap(values, color){
    var data = google.visualization.arrayToDataTable(values);

    var options = {
        colors: [color],
        keepAspectRatio: true,
    };

    var chart = new google.visualization.GeoChart(document.getElementById('countries-container'));

    chart.draw(data, options);
}

function makeCanvas(id) {
    var container = document.getElementById(id);
    var canvas = document.createElement('canvas');
    var ctx = canvas.getContext('2d');

    container.innerHTML = '';
    canvas.width = container.offsetWidth;
    canvas.height = container.offsetHeight;
    container.appendChild(canvas);

    return ctx;
}

