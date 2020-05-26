import Chart from "chart.js"
// import './gapi.js';

function loadDoughnut(name, reload = false){
    if (reload){
        $('#' + name + '-container')[0].innerHTML = "Chargement ...";
        $.post('/api/basic',
            {
                'start': loadStart(name),
                'method': name,
                'site_id': loadSiteId()
            }).done(function(data) {
            document.getElementById("data-" + name).dataset.values = JSON.stringify(data[loadSiteId()]);
            var container = document.getElementById("data-" + name).dataset.values;

            renderDoughnut(JSON.parse(container), getColors(), name);
        })
    }else{
        var container = document.getElementById("data-" + name).dataset.values;

        renderDoughnut(JSON.parse(container), getColors(), name);
    }

}

function getColors() {
    var colors = null;
    if ( document.getElementById("colors")){
        colors = document.getElementById("colors").dataset.colors;
        colors = JSON.parse(colors);
    }
    return colors;
}

function loadSiteId(){
    if (document.getElementById('select-website')){
        return document.getElementById('select-website').selectedOptions[0].value;
    }

    return null;
}

function loadStart(name){
    return $("#select_start_" + name)[0].selectedOptions[0].value;
}

function loadMap(reload){
    var countries = document.getElementById("data-countries").dataset.values;
    var map = document.getElementById("map_key").dataset.mapkey;
    var map_color = document.getElementById("map_color").dataset.mapcolor;
    var name = map ? 'countriesMap' : 'countriesChart'

    if (reload){
        $('#' + name + '-container')[0].innerHTML = "Chargement ...";
        $.post('/api/basic',
            {
                'start': document.getElementById(name + "-container").dataset.start,
                'method': name,
                'site_id': loadSiteId(),
            }).done(function(data) {

            document.getElementById("data-countries").dataset.values = JSON.stringify(data[loadSiteId()]);

            var countries = document.getElementById("data-countries").dataset.values;

            renderCountries(JSON.parse(countries), map_color , map);
        })
    }else{
        var countries = document.getElementById("data-countries").dataset.values;
        renderCountries(JSON.parse(countries), map_color    , map);
    }

}

function loadUserWeek(reload){
    var week_colors = {
        0 : null,
        1 : null
    };

    if ( document.getElementById("week_colors") != null){
        week_colors = document.getElementById("week_colors").dataset.weekcolors;
        week_colors = JSON.parse(week_colors);
    }

    var name = 'userWeek';

    if (reload){
        $('#' + name + '-container')[0].innerHTML = "Chargement ...";
        $.post('/api/users',
            {
                'method': name,
                'site_id': loadSiteId()
            }).done(function(data) {

            document.getElementById("data-" + name).dataset.values = JSON.stringify(data[loadSiteId()]);

            var weeks = document.getElementById("data-userWeek").dataset.values;

            renderWeekOverWeekChart(JSON.parse(weeks), week_colors);
        })
    }else{
        var weeks = document.getElementById("data-userWeek").dataset.values;

        renderWeekOverWeekChart(JSON.parse(weeks), week_colors);
    }

}

function loadUserYear(reload){
    var year_colors = {
        0 : null,
        1 : null
    };

    if ( document.getElementById("year_colors") != null){
        year_colors = document.getElementById("year_colors").dataset.yearcolors;
        year_colors = JSON.parse(year_colors);
    }

    var name = 'userYear';

    if (reload){
        $('#' + name + '-container')[0].innerHTML = "Chargement ...";
        $.post('/api/users',
            {
                'method': name,
                'site_id': loadSiteId()
            }).done(function(data) {

            document.getElementById("data-" + name).dataset.values = JSON.stringify(data[loadSiteId()]);

            var years = document.getElementById("data-userYear").dataset.values;

            renderYearOverYearChart(JSON.parse(years), year_colors);
        })
    }else{
        var years = document.getElementById("data-userYear").dataset.values;

        renderYearOverYearChart(JSON.parse(years), year_colors);
    }

}

function loadPages(reload = false){
    var name = 'pages';

    if (reload){
        $('#' + name + '-container')[0].innerHTML = "Chargement ...";
        $.post('/api/basic',
            {
                'start': loadStart(name),
                'method': name,
                'site_id': loadSiteId()
            }).done(function(data) {

            document.getElementById("data-" + name).dataset.values = JSON.stringify(data[loadSiteId()]);
            var container = document.getElementById("data-" + name).dataset.values;

            renderPages(JSON.parse(container));
        })
    }else{
        var container = document.getElementById("data-" + name).dataset.values;

        renderPages(JSON.parse(container));
    }

}

function loadUsers(reload = false){
    var name = 'users';
    var users_color = document.getElementById("users_color").dataset.userscolor;

    if (reload){
        $('#' + name + '-container')[0].innerHTML = "Chargement ...";
        $.post('/api/users',
            {
                'method': name,
                'site_id': loadSiteId()
            }).done(function(data) {

            document.getElementById("data-" + name).dataset.values = JSON.stringify(data[loadSiteId()]);
            var users = document.getElementById("data-users").dataset.values;
            renderUsers(JSON.parse(users), users_color, "users");
        })
    }else{
        var users = document.getElementById("data-users" ).dataset.values;
        renderUsers(JSON.parse(users), users_color, "users");
    }

}

function loadData(reload = false){

    var site_id = loadSiteId();

    if (!site_id) return;

    var colors = getColors();

    if (document.getElementById("browsers-container") != null){
        loadDoughnut('browsers', reload)
    }

    if (document.getElementById('userWeek-container') != null){
        loadUserWeek(reload)
    }

    if (document.getElementById('userYear-container') != null){
        loadUserYear(reload)
    }

    if (document.getElementById("sources-container") != null){
        loadDoughnut('sources', reload)
    }

    if (document.getElementById("devices-container") != null){
        loadDoughnut('devices', reload)
    }

    if (document.getElementById("pages-container") != null){
        loadPages(reload);
    }

    if (document.getElementById("countriesMap-container") != null || document.getElementById("countriesChart-container") != null){
        loadMap(reload)
    }

    if (document.getElementById("users-container") != null){
        loadUsers(reload);
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
                var width = (100 / item.data.length) + "%";
                var td = null;
                legendHtml.push('<table style="margin-bottom: 5px; margin-top: 30px"><tr>')

                if (chart.data.labels[0][1] !== null){
                    for (var i=0; i < item.data.length; i++) {
                        legendHtml.push('<td class="chart-legend" style="color:' + item.backgroundColor[i] +'"><i class="' + chart.data.labels[i][1] +'"></i></td>');
                    }
                    legendHtml.push('</tr>')

                    legendHtml.push('<tr>')
                }

                for (var i=0; i < item.data.length; i++) {
                    td = '<td class="chart-legend-label-text"';
                    td += 'style="width: ' + width;
                    if (chart.data.labels[i][1] == null){
                        td += ' ; color:' + item.backgroundColor[i] + '">';
                    }else{
                        td += '">';
                    }

                    td += chart.data.labels[i][0] +'</td>';
                    legendHtml.push(td)

                }
                legendHtml.push('</tr>')

                legendHtml.push('<tr>')
                for (var i=0; i < item.data.length; i++) {
                    var prct = parseFloat(chart.data.percents[i]).toFixed(2)
                    legendHtml.push('<td class="chart-legend-label-text" style="width: ' + width +' ">' + prct +' %</td>');
                }
                legendHtml.push('</tr>')

                legendHtml.push('<tr>')
                for (var i=0; i < item.data.length; i++) {
                    var prct = parseFloat(chart.data.diff[i]).toFixed(2)
                    var css = prct >= 0 ? 'text-success' : 'text-danger';
                    legendHtml.push('<td class="chart-legend-label-diff ' + css + ' " style="width: ' + width +' ">' + prct +' %</td>');
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
                backgroundColor: 'rgb(255,255,255,0)',
                borderDash: [10, 8],
                pointStrokeColor : '#fff',
                data :  data.values.last_week,
                fill: true
            },
            {
                label: 'Cette semaine',
                borderColor : colors[1],
                pointColor : colors[1],
                backgroundColor: 'rgb(255,255,255,0)',
                pointStrokeColor : '#fff',
                data : data.values.this_week,
                fill: true
            },
        ]
    };

    var  options = {
        tooltips: {
            mode: 'index',
            intersect: false,
            callbacks: {
                labelColor: function(tooltipItem, chart) {
                    return {
                        backgroundColor: colors[tooltipItem.datasetIndex]
                    }
                },
            }
        }
    };
    new Chart(makeCanvas('userWeek-container'), {
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

    var  options = {
        tooltips: {
            mode: 'index',
            intersect: false,
            callbacks: {
                labelColor: function(tooltipItem, chart) {
                    return {
                        backgroundColor: colors[tooltipItem.datasetIndex]
                    }
                },
            }
        }
    };
    new Chart(makeCanvas('userYear-container'), {
        type: 'bar',
        data: values,
        options: options
    });
}

function renderCountries(data, color, mapKey){
    if (mapKey){
        if (document.getElementById("countriesChart-container") != null){
            document.getElementById("countriesChart-container").remove()
        }
        google.charts.load('current', {
            'packages':['geochart'],
            'mapsApiKey': mapKey,
        });

        setTimeout(function() {
            google.charts.setOnLoadCallback(drawMap(data, color));
        }, 2000)

        $("#countriesMap-container").show()
    }else{
        if (document.getElementById("countriesMap-container") != null){
            document.getElementById("countriesMap-container").remove()
        }
        var values = {
            labels : data.labels,
            datasets : [
                {
                    label: "Nombre d'utilisateurs",
                    backgroundColor : color,
                    data : data.values
                }
            ]
        };

        var  options = {
            tooltips: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    labelColor: function(tooltipItem, chart) {
                        return {
                            backgroundColor: colors[tooltipItem.datasetIndex]
                        }
                    },
                }
            }
        };
        new Chart(makeCanvas('countriesChart-container'), {
            type: 'bar',
            data: values,
            options: options
        });
    }

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
    return color.substring(0, 17) + ", " + (prct) + ")";

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

    var chart = new google.visualization.GeoChart(document.getElementById('countriesMap-container'));

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

export {loadDoughnut, loadData, loadPages, loadUsers}
