

function get_memory_size(numEdges, numNodes) {
    var e1 = 53487657;
    var m1 = 782084360;
    var e2 = 61851248;
    var m2 = 902704108;
    var mx = (m2 - m1) / (e2 - e1);
    var b = m1 - e1 * mx;
    var m = mx * numEdges + b;
    m = Math.round(m / 1e6 * 1.02 + 0.5) + 10;// / 10;
    return m;
}

