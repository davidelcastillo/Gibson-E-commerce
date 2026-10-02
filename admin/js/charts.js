// Cargar Google Charts
google.charts.load('current', {packages: ['corechart']});

// Dibujar gráficos al cargar
google.charts.setOnLoadCallback(() => {
    setDefaultDates();
    updateCharts();
});

function setDefaultDates() {
    const startDateInput = document.getElementById("startDate");
    const endDateInput = document.getElementById("endDate");

    const today = new Date();
    const firstDayOfMonth = new Date(2024, 4, 1);

    startDateInput.value = firstDayOfMonth.toISOString().split('T')[0];
    endDateInput.value = today.toISOString().split('T')[0];
}

function validateCharts() {
    const startDateInput = document.getElementById("startDate").value;
    const endDateInput = document.getElementById("endDate").value;

    if (!startDateInput || !endDateInput) {
        Swal.fire({
            position: "top-end",
            icon: "error",
            title: "Error: Please select both start and end dates  ",
            showConfirmButton: false,
            timer: 1500,
            color: "#6f6d6b",
            background: "#0f0e0b",

        });
        return;
    }

    const startDate = new Date(startDateInput);
    const endDate = new Date(endDateInput);

    if (startDate > endDate) {
        Swal.fire({
            position: "top-end",
            icon: "error",
            title: "Error: Start date must be before end date  ",
            showConfirmButton: false,
            timer: 1500,
            color: "#6f6d6b",
            background: "#0f0e0b",

        });
        return;
    }

    updateCharts();
}

function updateCharts() {
    const startDate = document.getElementById("startDate").value;
    const endDate = document.getElementById("endDate").value;

    console.log("Aplicando filtros: ", startDate, endDate); // Verifica en la consola

    drawCharts(startDate, endDate);
}

function drawCharts(startDate = '', endDate = '') {
    // Gráfico de compras por cliente (Pie Chart)
    fetch(`../server/purchases_by_clients.php?start=${startDate}&end=${endDate}`)
        .then(response => response.json())
        .then(data => {
            if (data.length <= 1) { // Solo contiene encabezados, sin datos
                document.getElementById('clientsPurchasesChart').innerHTML = "<p style='color:white; text-align:center;'>No purchases found for the selected period.</p>";
                return;
            }
            const chartData = google.visualization.arrayToDataTable(data);

            const options = {
                title: 'Purchases by Clients',
                backgroundColor: '#282726', // Fondo oscuro
                titleTextStyle: {
                    fontSize: 18,
                    bold: true,
                    color: '#ffffff', // Color del título
                    fontName: 'Arial'
                },
                legend: {
                    textStyle: {
                        color: '#ffffff', // Color del texto en la leyenda
                        fontSize: 14
                    },
                    position: 'bottom'
                },
                pieSliceText: 'percentage',
                pieSliceTextStyle: {
                    color: '#ffffff' // Color del texto dentro del gráfico
                },
                colors: ['#24243a', '#373757', '#131335', '#040440'], // Tonalidades personalizadas
                chartArea: {
                    left: 20,
                    top: 50,
                    width: '90%',
                    height: '75%'
                }
            };

            const chart = new google.visualization.PieChart(document.getElementById('clientsPurchasesChart'));
            chart.draw(chartData, options);
        })
        .catch(error => console.error('Error fetching chart data:', error));

    // Gráfico de ganancias mensuales (Bar Chart)
    fetch(`../server/monthly_profits.php?start=${startDate}&end=${endDate}`)
        .then(response => response.json())
        .then(data => {
            if (data.length <= 1) { // Sin datos, solo encabezado
                document.getElementById('monthlyProfitsChart').innerHTML = "<p style='color:white; text-align:center;'>No profits found for the selected period.</p>";
                return;
            }
            const chartData = google.visualization.arrayToDataTable(data);

            const options = {
                title: 'Monthly Profits',
                backgroundColor: '#282726',
                titleTextStyle: {
                    fontSize: 18,
                    bold: true,
                    color: '#ffffff'
                },
                hAxis: {
                    title: 'Months',
                    textStyle: { color: '#ffffff' },
                    titleTextStyle: { color: '#ffffff' }
                },
                vAxis: {
                    title: 'Profits',
                    textStyle: { color: '#ffffff' },
                    titleTextStyle: { color: '#ffffff' }
                },
                legend: {
                    textStyle: {
                        color: '#ffffff'
                    },
                    position: 'bottom'
                },
                colors: ['#6a4c93'], // Tonalidad para las barras
                chartArea: {
                    left: 50,
                    top: 50,
                    width: '80%',
                    height: '75%'
                }
            };

            const chart = new google.visualization.BarChart(document.getElementById('monthlyProfitsChart'));
            chart.draw(chartData, options);
        })
        .catch(error => console.error('Error fetching chart data:', error));

    // Gráfico de ventas diarias (Line Chart)
    fetch(`../server/daily_sales.php?start=${startDate}&end=${endDate}`)
        .then(response => response.json())
        .then(data => {
            if (data.length <= 1) { // Sin datos
                document.getElementById('dailySalesChart').innerHTML = "<p style='color:white; text-align:center;'>No sales found for the selected period.</p>";
                return;
            }
            const chartData = google.visualization.arrayToDataTable(data);

            const options = {
                title: 'Daily Sales',
                backgroundColor: '#282726',
                titleTextStyle: {
                    fontSize: 18,
                    bold: true,
                    color: '#ffffff'
                },
                hAxis: {
                    title: 'Days',
                    textStyle: { color: '#ffffff' },
                    titleTextStyle: { color: '#ffffff' }
                },
                vAxis: {
                    title: 'Sales',
                    textStyle: { color: '#ffffff' },
                    titleTextStyle: { color: '#ffffff' }
                },
                legend: {
                    textStyle: {
                        color: '#ffffff'
                    },
                    position: 'bottom'
                },
                curveType: 'function',
                colors: ['#ff6f61'], // Tonalidad para la línea
                chartArea: {
                    left: 50,
                    top: 50,
                    width: '80%',
                    height: '75%'
                }
            };

            const chart = new google.visualization.LineChart(document.getElementById('dailySalesChart'));
            chart.draw(chartData, options);
        })
        .catch(error => console.error('Error fetching chart data:', error));
}
