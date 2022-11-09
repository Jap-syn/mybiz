function getDateRanges() {
    window.axios({
        url: '/dashboard/async/getDateRanges',
        method: 'POST',
        data: {}
    }).then(function(response) {
        var dateRanges = response.data.result;
        $('#dateRange > option').remove();
        $.each(dateRanges, function(key, value) {
            var $option = $('<option>').val(key).text(value);
            $('#dateRange').append($option);
        });
        $('#dateRange').append($('<option>').html('カレンダー選択').val('0'));
    }).catch(function(error) {
        console.log(error);
    });
}

function getReports() {
    getTiles();
    if (typeof window.chartOfReport !== 'undefined' && window.chartOfReport) {
        window.chartOfReport.destroy();
    }
    window.createCharts();
    getLists();
}

function getTiles() {
    window.axios({
        url: '/dashboard/async/getTiles',
        method: 'POST',
        data: {
            'account': document.querySelector('#account').value,
            'dateRange': document.querySelector('#dateRange').value,
            'startDate': document.querySelector('#startDate').value,
            'endDate': document.querySelector('#endDate').value
        }
    }).then(function(response) {
        var tiles = response.data.result;
        $('#dateRangeStartDate').text(tiles.dateRangeStartDate);
        $('#dateRangeEndDate').text(tiles.dateRangeEndDate);
        $('#totalSearchCount').text(tiles.totalSearchCount);
        $('#totalSearchCountUpdatedDate').text(tiles.locationReportUpdatedDate);
        $('#totalSearchCountAverage').text(tiles.totalSearchCountAverage);
        $('#totalSearchCountAverageUpdatedDate').text(tiles.locationReportUpdatedDate);
        $('#totalReviewCount').text(tiles.totalReviewCount);
        $('#totalReviewCountUpdatedDate').text(tiles.reviewUpdatedDate);
        $('#totalReviewCountUnreplied').text(tiles.totalReviewCountUnreplied);
        $('#totalReviewCountUnrepliedUpdatedDate').text(tiles.replyUpdatedDate);
        $('#locationReportUpdatedDate').text(tiles.locationReportUpdatedDate);
    }).catch(function(error) {
        console.log(error);
    });
};

function getCharts() {
    if (typeof window.chartOfReport !== 'undefined' && window.chartOfReport) {
        window.chartOfReport.destroy();
    }
    window.createCharts();
}

function getLists() {
    window.axios({
        url: '/dashboard/async/getLists',
        method: 'POST',
        data: {
            'account': document.querySelector('#account').value,
            'dateRange': document.querySelector('#dateRange').value,
            'startDate': document.querySelector('#startDate').value,
            'endDate': document.querySelector('#endDate').value
        }
    }).then(function(response) {
        var lists = response.data.result;
        $('#locationList tbody').remove();
        $('#locationList').append($('<tbody aria-live="polite" aria-relevant="all"></tbody>'));
        $.each(lists, function(index, list) {
            $('<tr role="row"></tr>')
                .append($('<td class="table-col-title" data-value="' + list.location_id + '"></td>').text(list.location_name))
                .append($('<td></td>').text(list.location_search_count_total))
                .append($('<td></td>').text(list.location_search_count_direct))
                .append($('<td></td>').text(list.location_search_count_indirect))
                .append($('<td></td>').text(list.location_search_count_chain))
                .append($('<td></td>').text(list.location_actions_count))
                .append($('<td></td>').text(list.location_average_rating))
                .append($('<td></td>').text(list.location_review_count))
                .append($('<td></td>').text(list.location_review_count_unreplied))
                .appendTo('#locationList');
        });
        $('#locationList').trigger('update');
    }).catch(function(error) {
        console.log(error);
    });
};

function exportPrepare() {
    'use strict';
    $('#exportAccount').val($('#account').val());
    $('#exportDateRange').val($('#dateRange').val());
    $('#exportStartDate').val($('#startDate').val());
    $('#exportEndDate').val($('#endDate').val());
    $('#exportChartType').val($('#chartType').val());
};

$(document).ready(function() {
    $.noConflict();
    const interval = $('#dateRangeInterval').val();
    const minDate = 1 - $('#dateRangeMinDate').val() - interval;
    const maxDate = $('#dateRangeMaxDate').val() - interval;

    const flatpickrConfig = {
        locale : 'ja',
        dateFormat: 'Y-m-d',
        allowInput: false,
        minDate: new Date().fp_incr(minDate),
        maxDate: new Date().fp_incr(maxDate)
    };

    flatpickr('#startDate', flatpickrConfig);
    flatpickr('#endDate', flatpickrConfig);

    $('#locationList').tablesorter({
        textExtraction: function(node) {
            var attr = $(node).attr('data-value');
            if(typeof attr !== 'undefined' && attr !== false) {
                return attr;
            }
            return $(node).text();
        }
    });

    if ($('#period').val() != 0) {
        getDateRanges();
    }

    $('#dateRange').change(function() {
        $('#startDate').prop('disabled', !$('option[value="0"]').prop('selected'));
        $('#endDate').prop('disabled', !$('option[value="0"]').prop('selected'));
    });
    $('#dateRange').trigger('change');
});