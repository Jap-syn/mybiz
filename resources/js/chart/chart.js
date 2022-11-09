const labelText1 = '全店舗合計数';
const labelText2 = '1店舗平均';
const labelText3 = '検索数';
const labelText4 = '直接検索数';
const labelText5 = '間接検索数';
const labelText6 = 'ブランド検索数';
const labelText7 = '検索数比率(％)';
const labelText8 = '直接検索数比率';
const labelText9 = '間接検索数比率';
const labelText10 = 'ブランド検索数比率';
const labelText11 = 'アクション数';
const labelText12 = 'ウェブサイトクリック数';
const labelText13 = '電話番号クリック数';
const labelText14 = 'ルート検索リクエスト数';
const labelText15 = 'アクション数比率';
const labelText16 = 'アクション数比率(％)';
const labelText17 = 'ウェブサイトクリック数比率';
const labelText18 = '電話番号クリック数比率';
const labelText19 = 'ルート検索リクエスト数比率';

var charts = [];
var chartAggregateDate = [];
var chartTotalSearchCount = [];
var chartTotalSearchCountAverage = [];
var chartTotalSearchCountDirect = [];
var chartTotalSearchCountIndirect = [];
var chartTotalSearchCountChain = [];
var chartTotalSearchCountDirectRatio = [];
var chartTotalSearchCountIndirectRatio = [];
var chartTotalSearchCountChainRatio = [];
var chartTotalActionCount = [];
var chartTotalActionCountWebsite = [];
var chartTotalActionCountPhone = [];
var chartTotalActionCountDrivingDirections = [];
var chartTotalActionCountWebsiteRatio = [];
var chartTotalActionCountPhoneRatio = [];
var chartTotalActionCountDrivingDirectionsRatio = [];
var chartTotalActionCountRatio = [];
var chartTotalNotActionCountRatio = [];

function createCharts() {
    charts = [];
    chartAggregateDate = [];
    chartTotalSearchCount = [];
    chartTotalSearchCountAverage = [];
    chartTotalSearchCountDirect = [];
    chartTotalSearchCountIndirect = [];
    chartTotalSearchCountChain = [];
    chartTotalSearchCountDirectRatio = [];
    chartTotalSearchCountIndirectRatio = [];
    chartTotalSearchCountChainRatio = [];
    chartTotalActionCount = [];
    chartTotalActionCountWebsite = [];
    chartTotalActionCountPhone = [];
    chartTotalActionCountDrivingDirections = [];
    chartTotalActionCountWebsiteRatio = [];
    chartTotalActionCountPhoneRatio = [];
    chartTotalActionCountDrivingDirectionsRatio = [];
    chartTotalActionCountRatio = [];
    chartTotalNotActionCountRatio = [];
    window.axios({
        url: '/dashboard/async/getCharts',
        method: 'POST',
        data: {
            'account': document.querySelector('#account').value,
            'dateRange': document.querySelector('#dateRange').value,
            'startDate': document.querySelector('#startDate').value,
            'endDate': document.querySelector('#endDate').value,
            'chartType': document.querySelector('#chartType').value
        }
    }).then(function(response) {
        charts = response.data.result;
        chartAggregateDate = charts.aggregateDate;
        chartTotalSearchCount = charts.totalSearchCount;
        chartTotalSearchCountAverage = charts.totalSearchCountAverage;
        chartTotalSearchCountDirect = charts.totalSearchCountDirect;
        chartTotalSearchCountIndirect = charts.totalSearchCountIndirect;
        chartTotalSearchCountChain = charts.totalSearchCountChain;
        chartTotalSearchCountDirectRatio = charts.totalSearchCountDirectRatio;
        chartTotalSearchCountIndirectRatio = charts.totalSearchCountIndirectRatio;
        chartTotalSearchCountChainRatio = charts.totalSearchCountChainRatio;
        chartTotalActionCount = charts.totalActionCount;
        chartTotalActionCountWebsite = charts.totalActionCountWebsite;
        chartTotalActionCountPhone = charts.totalActionCountPhone;
        chartTotalActionCountDrivingDirections = charts.totalActionCountDrivingDirections;
        chartTotalActionCountWebsiteRatio = charts.totalActionCountWebsiteRatio;
        chartTotalActionCountPhoneRatio = charts.totalActionCountPhoneRatio;
        chartTotalActionCountDrivingDirectionsRatio = charts.totalActionCountDrivingDirectionsRatio;
        chartTotalActionCountRatio = charts.totalActionCountRatio;
        chartTotalNotActionCountRatio = charts.totalNotActionCountRatio;
        createChartOfReport();
    }).catch(function(error) {
        console.log(error);
    });
};

function createChartOfReport() {
    var ctx = document.getElementById('chartOfReport').getContext('2d');
    var chartType = $('#chartType').val();
    $('#chart-legend').children().remove();
    switch (chartType) {
        case '0':
            window.chartOfReport = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartAggregateDate,
                    datasets: [
                        {
                            type: 'line',
                            label: labelText2,
                            borderColor: 'rgba(247, 225, 25, 1.0)',
                            borderWidth: 2,
                            fill: false,
                            lineTension: 0,
                            pointStyle: 'circle',
                            pointRadius: 2,
                            pointBorderColor: 'rgba(247, 225, 25, 1.0)',
                            pointBorderWidth: 2,
                            pointBackgroundColor: 'rgba(247, 225, 25, 1.0)',
                            pointHitRadius: 5,
                            pointHoverRadius: 5,
                            data: chartTotalSearchCountAverage,
                            yAxisID: 'rightItem'
                        },
                        {
                            label: labelText1,
                            borderColor: 'rgba(91, 192, 222, 0.8)',
                            borderWidth: 2,
                            backgroundColor: 'rgba(91, 192, 222, 0.8)',
                            data: chartTotalSearchCount,
                            yAxisID: 'leftItem'
                        }
                    ]
                },
                options: {
                    scales: {
                        yAxes: [
                            {
                                id: 'leftItem',
                                position: 'left',
                                ticks: {
                                    fontColor: 'rgba(91, 192, 222, 1.0)',
                                    beginAtZero: true,
                                    userCallback: function(label, index, labels) {
                                        if (Math.floor(label) === label) {
                                            return new Intl.NumberFormat().format(Math.floor(label));
                                        }
                                    }
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: labelText1,
                                    fontColor: 'rgba(159, 159, 159, 1.0)',
                                    fontSize: 14
                                },
                                gridLines: {
                                    drawBorder: true,
                                    color: 'rgba(91, 192, 222, 0.3)',
                                    zeroLineColor: 'rgba(204, 204, 204, 1.0)'
                                }
                            },
                            {
                                id: 'rightItem',
                                position: 'right',
                                ticks: {
                                    fontColor: 'rgba(247, 225, 25, 1.0)',
                                    beginAtZero: true,
                                    userCallback: function(label, index, labels) {
                                        if (Math.floor(label) === label) {
                                            return new Intl.NumberFormat().format(Math.floor(label));
                                        }
                                    }
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: labelText2,
                                    fontColor: 'rgba(159, 159, 159, 1.0)',
                                    fontSize: 14
                                },
                                gridLines: {
                                    drawBorder: true,
                                    color: 'rgba(247, 225, 25, 0.3)',
                                    zeroLineColor: 'rgba(204, 204, 204, 1.0)'
                                }
                            }
                        ],
                        xAxes: [
                            {
                                categoryPercentage: 0.8,
                                barPercentage: 0.5,
                                ticks: {
                                    padding: 20,
                                    fontColor: 'rgba(159, 159, 159, 1.0)'
                                },
                                gridLines: {
                                    drawBorder: true,
                                    color: 'rgba(0, 0, 0, 0.1)'
                                }
                            }
                        ]
                    },
                    tooltips: {
                        enabled: true,
                        position: 'nearest',
                        mode: 'index',
                        itemSort: function(first, second, data) {
                             return (second.datasetIndex - first.datasetIndex);
                        },
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var label = data.datasets[tooltipItem.datasetIndex].label || '';
                                if (label) {
                                    label += ' : ';
                                }
                                label += tooltipItem.yLabel.toLocaleString();
                                return label;
                            },
                            labelColor: function(tooltipItem, chartOfReport) {
                                var tooltipColor = chartOfReport.data.datasets[tooltipItem.datasetIndex].borderColor;
                                return {
                                    borderColor: tooltipColor,
                                    backgroundColor: tooltipColor
                                }
                            }
                        }
                    },
                    legend: {
                        display: false
                    }
                }
            });
            $('#chart-legend').append('<i class="fa fa-square text-info"></i>' + '<span>' + '&nbsp;' + labelText1 + '</span>');
            $('#chart-legend').append('<br>');
            $('#chart-legend').append('<i class="fa fa-square text-warning"></i>' + '<span>' + '&nbsp;' + labelText2 + '</span>');
            break;
        case '1':
            window.chartOfReport = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartAggregateDate,
                    datasets: [
                        {
                            label: labelText4,
                            borderColor: 'rgba(0, 123, 255, 0.8)',
                            borderWidth: 2,
                            backgroundColor: 'rgba(0, 123, 255, 0.8)',
                            data: chartTotalSearchCountDirect
                        },
                        {
                            label: labelText5,
                            borderColor: 'rgba(247, 225, 25, 0.8)',
                            borderWidth: 2,
                            backgroundColor: 'rgba(247, 225, 25, 0.8)',
                            data: chartTotalSearchCountIndirect
                        },
                        {
                            label: labelText6,
                            borderColor: 'rgba(108, 117, 125, 0.8)',
                            borderWidth: 2,
                            backgroundColor: 'rgba(108, 117, 125, 0.8)',
                            data: chartTotalSearchCountChain
                        }
                    ]
                },
                options: {
                    scales: {
                        yAxes: [
                            {
                                stacked: true,
                                ticks: {
                                    fontColor: 'rgba(159, 159, 159, 1.0)',
                                    beginAtZero: true,
                                    userCallback: function(label, index, labels) {
                                        if (Math.floor(label) === label) {
                                            return new Intl.NumberFormat().format(Math.floor(label));
                                        }
                                    }
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: labelText3,
                                    fontColor: 'rgba(159, 159, 159, 1.0)',
                                    fontSize: 14
                                },
                                gridLines: {
                                    drawBorder: true,
                                    color: 'rgba(0, 0, 0, 0.1)',
                                    zeroLineColor: 'rgba(204, 204, 204, 1.0)'
                                }
                            }
                        ],
                        xAxes: [
                            {
                                categoryPercentage: 0.8,
                                barPercentage: 0.5,
                                stacked: true,
                                ticks: {
                                    padding: 20,
                                    fontColor: 'rgba(159, 159, 159, 1.0)'
                                },
                                gridLines: {
                                    drawBorder: true,
                                    color: 'rgba(0, 0, 0, 0.1)'
                                }
                            }
                        ]
                    },
                    tooltips: {
                        enabled: true,
                        position: 'nearest',
                        mode: 'index',
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var label = data.datasets[tooltipItem.datasetIndex].label || '';
                                if (label) {
                                    label += ' : ';
                                }
                                label += tooltipItem.yLabel.toLocaleString();
                                return label;
                            },
                            labelColor: function(tooltipItem, chartOfReport) {
                                var tooltipColor = chartOfReport.data.datasets[tooltipItem.datasetIndex].borderColor;
                                return {
                                    borderColor: tooltipColor,
                                    backgroundColor: tooltipColor
                                }
                            }
                        }
                    },
                    legend: {
                        display: false
                    }
                }
            });
            $('#chart-legend').append('<i class="fa fa-square text-primary"></i>' + '<span>' + '&nbsp;' + labelText4 + '</span>');
            $('#chart-legend').append('<br>');
            $('#chart-legend').append('<i class="fa fa-square text-warning"></i>' + '<span>' + '&nbsp;' + labelText5 + '</span>');
            $('#chart-legend').append('<br>');
            $('#chart-legend').append('<i class="fa fa-square text-secondary"></i>' + '<span>' + '&nbsp;' + labelText6 + '</span>');
            break;
        case '2':
            window.chartOfReport = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartAggregateDate,
                    datasets: [
                        {
                            label: labelText8,
                            borderColor: 'rgba(0, 123, 255, 0.8)',
                            borderWidth: 2,
                            backgroundColor: 'rgba(0, 123, 255, 0.8)',
                            data: chartTotalSearchCountDirectRatio
                        },
                        {
                            label: labelText9,
                            borderColor: 'rgba(247, 225, 25, 0.8)',
                            borderWidth: 2,
                            backgroundColor: 'rgba(247, 225, 25, 0.8)',
                            data: chartTotalSearchCountIndirectRatio
                        },
                        {
                            label: labelText10,
                            borderColor: 'rgba(108, 117, 125, 0.8)',
                            borderWidth: 2,
                            backgroundColor: 'rgba(108, 117, 125, 0.8)',
                            data: chartTotalSearchCountChainRatio
                        }
                    ]
                },
                options: {
                    scales: {
                        yAxes: [
                            {
                                stacked: true,
                                ticks: {
                                    min: 0,
                                    max: 100,
                                    stepSize: 10,
                                    fontColor: 'rgba(159, 159, 159, 1.0)',
                                    beginAtZero: true
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: labelText7,
                                    fontColor: 'rgba(159, 159, 159, 1.0)',
                                    fontSize: 14
                                },
                                gridLines: {
                                    drawBorder: true,
                                    color: 'rgba(0, 0, 0, 0.1)',
                                    zeroLineColor: 'rgba(204, 204, 204, 1.0)'
                                }
                            }
                        ],
                        xAxes: [
                            {
                                categoryPercentage: 0.8,
                                barPercentage: 0.5,
                                stacked: true,
                                ticks: {
                                    padding: 20,
                                    fontColor: 'rgba(159, 159, 159, 1.0)'
                                },
                                gridLines: {
                                    drawBorder: true,
                                    color: 'rgba(0, 0, 0, 0.1)'
                                }
                            }
                        ]
                    },
                    tooltips: {
                        enabled: true,
                        position: 'nearest',
                        mode: 'index',
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var label = data.datasets[tooltipItem.datasetIndex].label || '';
                                if (label) {
                                    label += ' : ';
                                }
                                label += tooltipItem.yLabel.toLocaleString();
                                return label;
                            },
                            labelColor: function(tooltipItem, chartOfReport) {
                                var tooltipColor = chartOfReport.data.datasets[tooltipItem.datasetIndex].borderColor;
                                return {
                                    borderColor: tooltipColor,
                                    backgroundColor: tooltipColor
                                }
                            }
                        }
                    },
                    legend: {
                        display: false
                    }
                }
            });
            $('#chart-legend').append('<i class="fa fa-square text-primary"></i>' + '<span>' + '&nbsp;' + labelText8 + '</span>');
            $('#chart-legend').append('<br>');
            $('#chart-legend').append('<i class="fa fa-square text-warning"></i>' + '<span>' + '&nbsp;' + labelText9 + '</span>');
            $('#chart-legend').append('<br>');
            $('#chart-legend').append('<i class="fa fa-square text-secondary"></i>' + '<span>' + '&nbsp;' + labelText10 + '</span>');
            break;
       case '3':
            window.chartOfReport = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartAggregateDate,
                    datasets: [
                        {
                            label: labelText12,
                            borderColor: 'rgba(0, 123, 255, 0.8)',
                            borderWidth: 2,
                            backgroundColor: 'rgba(0, 123, 255, 0.8)',
                            data: chartTotalActionCountWebsite
                        },
                        {
                            label: labelText13,
                            borderColor: 'rgba(247, 225, 25, 0.8)',
                            borderWidth: 2,
                            backgroundColor: 'rgba(247, 225, 25, 0.8)',
                            data: chartTotalActionCountPhone
                        },
                        {
                            label: labelText14,
                            borderColor: 'rgba(108, 117, 125, 0.8)',
                            borderWidth: 2,
                            backgroundColor: 'rgba(108, 117, 125, 0.8)',
                            data: chartTotalActionCountDrivingDirections
                        }
                    ]
                },
                options: {
                    scales: {
                        yAxes: [
                            {
                                stacked: true,
                                ticks: {
                                    fontColor: 'rgba(159, 159, 159, 1.0)',
                                    beginAtZero: true,
                                    userCallback: function(label, index, labels) {
                                        if (Math.floor(label) === label) {
                                            return new Intl.NumberFormat().format(Math.floor(label));
                                        }
                                    }
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: labelText11,
                                    fontColor: 'rgba(159, 159, 159, 1.0)',
                                    fontSize: 14
                                },
                                gridLines: {
                                    drawBorder: true,
                                    color: 'rgba(0, 0, 0, 0.1)',
                                    zeroLineColor: 'rgba(204, 204, 204, 1.0)'
                                }
                            }
                        ],
                        xAxes: [
                            {
                                categoryPercentage: 0.8,
                                barPercentage: 0.5,
                                stacked: true,
                                ticks: {
                                    padding: 20,
                                    fontColor: 'rgba(159, 159, 159, 1.0)'
                                },
                                gridLines: {
                                    drawBorder: true,
                                    color: 'rgba(0, 0, 0, 0.1)'
                                }
                            }
                        ]
                    },
                    tooltips: {
                        enabled: true,
                        position: 'nearest',
                        mode: 'index',
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var label = data.datasets[tooltipItem.datasetIndex].label || '';
                                if (label) {
                                    label += ' : ';
                                }
                                label += tooltipItem.yLabel.toLocaleString();
                                return label;
                            },
                            labelColor: function(tooltipItem, chartOfReport) {
                                var tooltipColor = chartOfReport.data.datasets[tooltipItem.datasetIndex].borderColor;
                                return {
                                    borderColor: tooltipColor,
                                    backgroundColor: tooltipColor
                                }
                            }
                        }
                    },
                    legend: {
                        display: false
                    }
                }
            });
            $('#chart-legend').append('<i class="fa fa-square text-primary"></i>' + '<span>' + '&nbsp;' + labelText12 + '</span>');
            $('#chart-legend').append('<br>');
            $('#chart-legend').append('<i class="fa fa-square text-warning"></i>' + '<span>' + '&nbsp;' + labelText13 + '</span>');
            $('#chart-legend').append('<br>');
            $('#chart-legend').append('<i class="fa fa-square text-secondary"></i>' + '<span>' + '&nbsp;' + labelText14 + '</span>');
            break;
        case '4':
            window.chartOfReport = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartAggregateDate,
                    datasets: [
                        {
                            label: labelText17,
                            borderColor: 'rgba(0, 123, 255, 0.8)',
                            borderWidth: 2,
                            backgroundColor: 'rgba(0, 123, 255, 0.8)',
                            data: chartTotalActionCountWebsiteRatio
                        },
                        {
                            label: labelText18,
                            borderColor: 'rgba(247, 225, 25, 0.8)',
                            borderWidth: 2,
                            backgroundColor: 'rgba(247, 225, 25, 0.8)',
                            data: chartTotalActionCountPhoneRatio
                        },
                        {
                            label: labelText19,
                            borderColor: 'rgba(108, 117, 125, 0.8)',
                            borderWidth: 2,
                            backgroundColor: 'rgba(108, 117, 125, 0.8)',
                            data: chartTotalActionCountDrivingDirectionsRatio
                        }
                    ]
                },
                options: {
                    scales: {
                        yAxes: [
                            {
                                stacked: true,
                                ticks: {
                                    min: 0,
                                    max: 100,
                                    stepSize: 10,
                                    fontColor: 'rgba(159, 159, 159, 1.0)',
                                    beginAtZero: true
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: labelText16,
                                    fontColor: 'rgba(159, 159, 159, 1.0)',
                                    fontSize: 14
                                },
                                gridLines: {
                                    drawBorder: true,
                                    color: 'rgba(0, 0, 0, 0.1)',
                                    zeroLineColor: 'rgba(204, 204, 204, 1.0)'
                                }
                            }
                        ],
                        xAxes: [
                            {
                                categoryPercentage: 0.8,
                                barPercentage: 0.5,
                                stacked: true,
                                ticks: {
                                    padding: 20,
                                    fontColor: 'rgba(159, 159, 159, 1.0)'
                                },
                                gridLines: {
                                    drawBorder: true,
                                    color: 'rgba(0, 0, 0, 0.1)'
                                }
                            }
                        ]
                    },
                    tooltips: {
                        enabled: true,
                        position: 'nearest',
                        mode: 'index',
                        callbacks: {
                            label: function(tooltipItem, data) {
                                var label = data.datasets[tooltipItem.datasetIndex].label || '';
                                if (label) {
                                    label += ' : ';
                                }
                                label += tooltipItem.yLabel.toLocaleString();
                                return label;
                            },
                            labelColor: function(tooltipItem, chartOfReport) {
                                var tooltipColor = chartOfReport.data.datasets[tooltipItem.datasetIndex].borderColor;
                                return {
                                    borderColor: tooltipColor,
                                    backgroundColor: tooltipColor
                                }
                            }
                        }
                    },
                    legend: {
                        display: false
                    }
                }
            });
            $('#chart-legend').append('<i class="fa fa-square text-primary"></i>' + '<span>' + '&nbsp;' + labelText17 + '</span>');
            $('#chart-legend').append('<br>');
            $('#chart-legend').append('<i class="fa fa-square text-warning"></i>' + '<span>' + '&nbsp;' + labelText18 + '</span>');
            $('#chart-legend').append('<br>');
            $('#chart-legend').append('<i class="fa fa-square text-secondary"></i>' + '<span>' + '&nbsp;' + labelText19 + '</span>');
            break;
        case '5':
            window.chartOfReport = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartAggregateDate,
                    datasets: [
                        {
                            label: labelText15,
                            borderColor: 'rgba(220, 53, 69, 0.8)',
                            borderWidth: 2,
                            backgroundColor: 'rgba(220, 53, 69, 0.8)',
                            data: chartTotalActionCountRatio
                        },
                        {
                            label: '',
                            borderColor: 'rgba(108, 117, 125, 0.2)',
                            borderWidth: 2,
                            backgroundColor: 'rgba(108, 117, 125, 0.2)',
                            data: chartTotalNotActionCountRatio
                        }
                    ]
                },
                options: {
                    scales: {
                        yAxes: [
                            {
                                stacked: true,
                                ticks: {
                                    min: 0,
                                    max: 100,
                                    stepSize: 10,
                                    fontColor: 'rgba(159, 159, 159, 1.0)',
                                    beginAtZero: true
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: labelText16,
                                    fontColor: 'rgba(159, 159, 159, 1.0)',
                                    fontSize: 14
                                },
                                gridLines: {
                                    drawBorder: true,
                                    color: 'rgba(0, 0, 0, 0.1)',
                                    zeroLineColor: 'rgba(204, 204, 204, 1.0)'
                                }
                            }
                        ],
                        xAxes: [
                            {
                                categoryPercentage: 0.8,
                                barPercentage: 0.5,
                                stacked: true,
                                ticks: {
                                    padding: 20,
                                    fontColor: 'rgba(159, 159, 159, 1.0)'
                                },
                                gridLines: {
                                    drawBorder: true,
                                    color: 'rgba(0, 0, 0, 0.1)'
                                }
                            }
                        ]
                    },
                    tooltips: {
                        enabled: true,
                        position: 'nearest',
                        mode: 'index',
                        callbacks: {
                            label: function(tooltipItem, data) {
                                if (tooltipItem.datasetIndex === 0) {
                                    var label = data.datasets[tooltipItem.datasetIndex].label || '';
                                    if (label) {
                                        label += ' : ';
                                    }
                                    label += tooltipItem.yLabel.toLocaleString();
                                    return label;
                                }
                            },
                            labelColor: function(tooltipItem, chartOfReport) {
                                if (tooltipItem.datasetIndex === 0) {
                                    var tooltipColor = chartOfReport.data.datasets[tooltipItem.datasetIndex].borderColor;
                                    return {
                                        borderColor: tooltipColor,
                                        backgroundColor: tooltipColor
                                    }
                                }
                            }
                        }
                    },
                    legend: {
                        display: false
                    }
                }
            });
            $('#chart-legend').append('<i class="fa fa-square text-danger"></i>' + '<span>' + '&nbsp;' + labelText15 + '</span>');
            break;
    }
};

$(document).ready(function() {
    createCharts();
});