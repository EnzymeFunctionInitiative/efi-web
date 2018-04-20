

// Private
function getFamilyCountsRaw(familyInputId, countOutputId, handler, useUniref90, useUniref50) {
    var family = document.getElementById(familyInputId).value;

    if ((family.toLowerCase().startsWith("cl") && family.length == 6) || family.length >= 7) {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200 && this.responseText.length > 1) {
                handler(this.responseText, countOutputId, useUniref90, useUniref50);
            }
        };
        family_query = family.replace(/\n/g, " ").replace(/\r/g, " ");
        xmlhttp.open("GET", "get_family_counts.php?families=" + family_query, true);
        xmlhttp.send();
    }
}

function getFamilyCountsTableHandler(responseText, countOutputId, useUniref90, useUniref50) {

    var data = JSON.parse(responseText);

    var sumCounts = {all: 0, uniref90: 0, uniref50: 0};
    var table = document.getElementById(countOutputId);
    var newBody = document.createElement('tbody');

    for (famId in data) {
        var cellIdx = 0;
        var row = newBody.insertRow(-1);
        var familyCell = row.insertCell(cellIdx++);
        familyCell.innerHTML = famId;
        var familyNameCell = row.insertCell(cellIdx++);
        familyNameCell.innerHTML = data[famId].name;

        var countVal = data[famId].all;
        var countCell = row.insertCell(cellIdx++);
        countCell.innerHTML = commaFormatted(countVal.toString());
        countCell.style.textAlign = "right";
        sumCounts.all += parseInt(countVal);
        
        if (useUniref90) {
            if (data[famId].uniref90) {
                countVal = data[famId].uniref90;
                countCell = row.insertCell(cellIdx++);
                countCell.innerHTML = commaFormatted(countVal.toString());
                countCell.style.textAlign = "right";
                sumCounts.uniref90 += parseInt(countVal);
            } else {
                countCell = row.insertCell(cellIdx++);
                countCell.innerHTML = "0";
                countCell.style.textAlign = "right";
            }
        }
        
        if (useUniref50 && typeof data[famId].uniref50 !== 'undefined') {
            countVal = data[famId].uniref50;
            countCell = row.insertCell(cellIdx++);
            countCell.innerHTML = commaFormatted(countVal.toString());
            countCell.style.textAlign = "right";
            sumCounts.uniref50 += parseInt(countVal);
        }
    }

    var cellIdx = 0;
    var row = newBody.insertRow(-1);
    var empty = row.insertCell(cellIdx++);

    var total1 = row.insertCell(cellIdx++);
    total1.innerHTML = "Total:";
    total1.style.textAlign = "right";

    var total2 = row.insertCell(cellIdx++);
    total2.innerHTML = commaFormatted(sumCounts.all.toString());
    total2.style.textAlign = "right";

    if (useUniref90) {
        var total3 = row.insertCell(cellIdx++);
        total3.innerHTML = commaFormatted(sumCounts.uniref90.toString());
        total3.style.textAlign = "right";
    }

    if (useUniref50) {
        var total4 = row.insertCell(cellIdx++);
        total4.innerHTML = commaFormatted(sumCounts.uniref50.toString());
        total4.style.textAlign = "right";
    }

    table.parentNode.replaceChild(newBody, table);
    newBody.id = countOutputId;

    return sumCounts.all;
}

function commaFormatted(num) {

    if (!num || num.length <= 3)
        return num;

    var formatted = "";

    while (num.length > 3) {
        var part = num.substring(num.length - 3, num.length);
        formatted = part + "," + formatted;
        num = num.substring(0, num.length - 3);
    }
    
    if (num.length > 0)
        formatted = num + "," + formatted;
    formatted = formatted.substring(0, formatted.length - 1);

    return formatted;
}

function getFamilyCounts(familyInputId, countOutputId) {
    getFamilyCountsRaw(familyInputId, countOutputId, getFamilyCountsTableHandler);
}

function checkFamilyInput(familyInputId, containerOutputId, countOutputId, warningId, warningThreshold, useUniref90, useUniref50) {
    var input = document.getElementById(familyInputId).value;
    var container = document.getElementById(containerOutputId);
    var warning = document.getElementById(warningId);

    var thresholdNum = 7;
    if (input.toLowerCase().startsWith("cl"))
        thresholdNum = 6;
    if (input.length < thresholdNum) {
        warning.style.color = "black";
        container.style.display = "none";
        return;
    }

    var handleResponse = function(responseText, countOutputId) {
        var sumCounts = getFamilyCountsTableHandler(responseText, countOutputId, useUniref90, useUniref50);
        if (sumCounts > warningThreshold)
            warning.style.color = "red";
        else
            warning.style.color = "black";
        container.style.display = "block";
    };

    getFamilyCountsRaw(familyInputId, countOutputId, handleResponse, useUniref90, useUniref50);
}

// Public
function checkUniRef90Requirement(familyInputId, useUnirefId, fractionId, continueWarningFn) {
    var input = document.getElementById(familyInputId).value;
    var isUnirefElem = document.getElementById(useUnirefId);
    var fractionElem = document.getElementById(fractionId);
    var isUnirefChecked = false;
    if (isUnirefElem)
        isUnirefChecked = isUnirefElem.checked;
    var fraction = 1;
    if (fractionElem)
        fraction = fractionElem.value;

    // If UniRef90 is checked, then continue without checking the family size
    if (isUnirefChecked) {
        continueWarningFn();
        return;
    }

    var onCheckFn = function() {
    };

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200 && this.responseText.length > 1) {
            var data = JSON.parse(this.responseText);
            var fraction = typeof data.total_fraction !== "undefined" ? data.total_fraction : 0;
            if (data.is_warning)
                showUniRefRequirement(data.total, fraction, continueWarningFn);
            else
                continueWarningFn();
        }
    };
    var family_query = input.replace(/\n/g, " ").replace(/\r/g, " ");
    xmlhttp.open("GET", "get_family_counts.php?check-warning=1&families=" + family_query + "&fraction=" + fraction, true);
    xmlhttp.send();
}

// Private
function showUniRefRequirement(numFound, numAfterFraction, continueFn) {
    var warningDialog = $("#family-warning");
    $("#family-warning-total-size").text(commaFormatted(numFound.toString()));
    if (numAfterFraction > 0) {
        $("#family-warning-fraction-size").text(" (" + commaFormatted(numAfterFraction.toString()) + " after applying a fraction)");
    }

    var warningOkFn = function() {
        $(this).dialog("close");
        continueFn(); // this is a callback from the submit.js functions that allows the submission to continue
    };

    var warningCancelFn = function() {
        $(this).dialog("close");
    };

    warningDialog.dialog({resizeable: false, draggable: false, autoOpen: false, height: 350, width: 500,
        buttons: { "Ok": warningOkFn, "Cancel": warningCancelFn }
    });

    warningDialog.dialog("open");
}

