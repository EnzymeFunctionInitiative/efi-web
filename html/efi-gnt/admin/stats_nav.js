
var Month = 0;
var Year = 0;

function setMonth(month) {
    Month = month;
}
function setYear(year) {
    Year = year;
}
function getMonth() { return Month; }
function getYear() { return Year; }


var decMonth = function() {
    Month--;
    if (Month < 1) {
        Month = 12;
        Year--;
    }
    $(".month-sel").val(Month);
    $(".year-sel").val(Year);
};

var incMonth = function() {
    Month++;
    if (Month > 12) {
        Month = 1;
        Year++;
    }
    $(".month-sel").val(Month);
    $(".year-sel").val(Year);
};


