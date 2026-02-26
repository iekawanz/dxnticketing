// Load Google Charts
google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(loadData);

function loadData()
{
    var about = $('#aboutFilter').val();
    var country = $('#countryFilter').val();

    var postData =  'about='+encodeURIComponent(about) +
                    '&country='+encodeURIComponent(country) +
                    '&func=getData';

    $.ajax({
        type: "POST",
        url: "actions/Dashboard.php",
        async:true,
        data: postData,
        success: function(msg,ret){
            
            if( ret != 'success' )
            {
                return;
            }
            try {
                var result = eval( '(' + msg +')' );

                if( result.status == '1' )
                {  
                    //Ticket Analysis
                    drawChart(result.data.months, result.data.inProgressData, result.data.completedData);
                    $('#total_in_progress').text(result.data.total_in_progress);
                    $('#total_completed').text(result.data.total_completed);
                    $('#average_rating').text(result.data.average_rating);

                    //Today Task
                    drawChart2(result.data.task_completed, result.data.task_todo);
                }
                else
                {
                    return false;
                }
            }
            catch(E)
            {
                return;   
            } 

        }
    });
}


function drawChart(months, inProgressData, completedData) 
{

    var dataArray = [['Month', 'Open', 'Closed']];

    for (var i = 0; i < months.length; i++) {
        dataArray.push([months[i], inProgressData[i], completedData[i]]);
    }

    var data = google.visualization.arrayToDataTable(dataArray);

    var options = {
        colors: ['#3a76ef','#2FBFCE'],
        backgroundColor: { fill:'transparent' },
        width: ['100%'],
        chart: {
          title: '',
        }
      };

    var chart = new google.charts.Bar(document.getElementById('columnchart_material'));

    chart.draw(data, google.charts.Bar.convertOptions(options));
}

function drawChart2(completed, todo) {

    var data2 = google.visualization.arrayToDataTable([
      ['Task', 'Hours per Day'],
      ['Completed', parseInt(completed)],
      ['ToDo', parseInt(todo)]
    ]);

    var options2 = {
      title: '',
      pieHole: 0.4,
      colors: ['#3a76ef','#2FBFCE'],
      backgroundColor: { fill:'transparent' },
      width: ['100%'],
    };

    var chart2 = new google.visualization.PieChart(document.getElementById('donutchart'));
    chart2.draw(data2, options2);
}