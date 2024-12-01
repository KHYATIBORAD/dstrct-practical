<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Score Graph</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-5">User Score Graph</h1>

        <div class="col-md-3 mb-3">
            <label for="user_id" class="form-label">Filter by User:</label>
            <select name="user_id" id="user_id" class="form-select">
                <option value="">All Users</option>
                <?php foreach ($users as $key => $value) { ?>
                    <option value="<?php echo $value['id'] ?>"><?php echo $value['full_name'] ?></option>
                <?php } ?>
            </select>
        </div>

        <canvas id="scoreChart" width="400" height="200"></canvas>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let chart;

        // Function to fetch data and update the graph
        function updateGraph(userId = '') {
            $.ajax({
                url: 'get-chart-data',
                type: 'POST',
                data: {
                    user_id: userId
                },
                success: function (data) {
                    var jsonData = JSON.parse(data);

                    const labels = jsonData.map(item => item.score_date);
                    const scores = jsonData.map(item => item.score);

                    if (chart) {
                        chart.destroy();
                    }

                    const ctx = document.getElementById('scoreChart').getContext('2d');
                    chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'User Scores',
                                data: scores,
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 2,
                                fill: false
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching data:', error);
                }
            });
        }

        $(function () {
            updateGraph();

            $('#user_id').on('change', function () {
                const userId = $(this).val();
                updateGraph(userId);
            });
        });
    </script>
</body>

</html>