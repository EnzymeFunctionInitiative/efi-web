function addDownloadLinks() {
    // adds an unordered list of download links (2 items, PNG and SVG) to the
    // Sunburst-viz tab underneath the buttons for downloading raw data
    //
    // this should be called as soon as all of the page elements are loaded
    // so that the links are present. calling too early could causes errors if
    // the elements that these functions target have not been added to the page yet

    // create link for PNG
    const pngDownloadLink = document.createElement('a')
    pngDownloadLink.id = "pngLink";
    pngDownloadLink.href = "javascript:void(0)";
    pngDownloadLink.download = "taxonomy.png";
    pngDownloadLink.innerHTML = "Download PNG";

    // create link for SVG
    const svgDownloadLink = document.createElement('a')
    svgDownloadLink.id = "svgLink";
    svgDownloadLink.href = "javascript:void(0)";
    svgDownloadLink.download = "taxonomy.svg";
    svgDownloadLink.innerHTML = "Download SVG";

    // mr-auto is the class that holds the data download button div
    const mr_auto = document.getElementsByClassName("mr-auto")[0];

    // construct the new elements to add for image downloads in their own div
    const downloadDiv = document.createElement("div");
    downloadDiv.setAttribute("id", "sunburst-viz-image-download");
    const listOfDownloads = document.createElement("ul");
    const link1 = document.createElement('li');
    const link2 = document.createElement('li');

    // put <a> tags in list items
    link1.appendChild(pngDownloadLink);
    link2.appendChild(svgDownloadLink);

    // add list items to unordered list
    listOfDownloads.appendChild(link1);
    listOfDownloads.appendChild(link2);

    // add unordered list to div
    downloadDiv.appendChild(listOfDownloads);

    // create a light horizontal rule to match style of page
    const lightHR = document.createElement("hr");
    lightHR.setAttribute("class", "light");

    // add light horizontal rule then div with new links
    mr_auto.appendChild(lightHR);
    mr_auto.appendChild(downloadDiv);

    // update some fields in the SVG definition to make it render correctly, see below
    fixSVG();

    // establish a callback that will create a PNG out of the sunburst visualization
    // when the "Download PNG" link is clicked
    document.getElementById("pngLink").addEventListener('click', e => {
        e.target.href = createPNG("sunburst-viz");
    });

    // establish a callback that will create a downloadable SVG out of the sunburst 
    // visualization when the "Download SVG" link is clicked
    document.getElementById("svgLink").addEventListener('click', e => {
        e.target.href = createSVG("sunburst-viz");
    })
}

function fixSVG() {
    // The SVG created by the sunburst.js library uses some outdated attributes.
    // this function updates them, but also crucially adds a <style> section which
    // helps render the text properly. The stylesheet included here was pulled out
    // of the minified version of the library at 
    // https://github.com/EnzymeFunctionInitiative/sunburst/blob/main/web/js/sunburst-chart.min.js
    //
    // This is a hacky solution and it would be better to fix the library to properly include
    // the stylesheet
    const svg = document.getElementsByClassName("sunburst-viz")[0].querySelector('svg');
    svg.removeAttribute("style");
    svg.setAttribute("fill", "none");
    svg.setAttribute("width", "600px")
    svg.setAttribute("height", "600px")
    // https://stackoverflow.com/a/27077840
    // https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/xlink:href
    svg.setAttribute("xmlns:xlink", "http://www.w3.org/1999/xlink");

    // add the missing stylesheet if it is not there
    if (svg.childNodes[0].nodeName != "STYLE") {
        svgStyle = document.createElement("style");
        svgStyle.innerHTML = "text {font-family: sans-serif;font-size: 12px;dominant-baseline: middle;text-anchor: middle;pointer-events: none;fill: #222;}.text-contour {fill: none;stroke: white;stroke-width: 5;stroke-linejoin: round;}.main-arc {stroke-width: 1px;}.hidden-arc {fill: none;}"
        svg.prepend(svgStyle)
    }
}

function serializeSVG(svgParentElement) {
    // turns the embedded SVG object into a regular string
    const svg = document.getElementsByClassName(svgParentElement)[0].querySelector('svg');
    svgData = new XMLSerializer().serializeToString(svg);
    // svgData = svgData.replace("xlink:href", "href");
    return svgData;
}

function createSVG(svgParentElement) {
    // base64 encodes serialized version of SVG and prepends a header so that
    // browswers know what to do with it

    // Data header for a svg image: 
    const dataHeader = 'data:image/svg+xml;base64,';
    const imgBase64 = dataHeader + btoa(serializeSVG(svgParentElement));

    return imgBase64;
}

function createPNG(svgParentElement) {
    // uses an HTML canvas to convert the base64-encoded SVG into a PNG
    // result is a base64 encoded string which browers know how to download 
    // into an image file
    const imgBase64 = createSVG(svgParentElement);

    // store in an img to supply to the canvas
    const tempImg = document.createElement('img');
    tempImg.src = imgBase64;
    console.log(tempImg)

    // use an html canvas to convert base64 encoded svg to PNG
    const svg = document.getElementsByClassName(svgParentElement)[0].querySelector('svg');
    const canvas = document.createElement('canvas');
    canvas.width = svg.clientWidth;
    canvas.height = svg.clientHeight;
    canvas.getContext('2d').drawImage(tempImg, 0, 0, svg.clientWidth, svg.clientHeight);
    document.getElementById("sunburst-viz-image-download").append(canvas);
    const dataURL = canvas.toDataURL()

    console.log(dataURL);

    return dataURL;

};