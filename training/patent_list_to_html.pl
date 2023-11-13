#!/bin/env perl

use strict;
use warnings;



my $inc = 1;

my $header = <>;

my $html = "";

while (<>) {
    next if m/^#/;
    chomp;
    my @p = split(m/\t/);
    foreach my $i (0..$#p) { $p[$i] =~ s/^\s*(.*?)\s*$/$1/; }
    #id title   assignee    inventor/author priority date   filing/creation date    publication date    grant date  result link representative figure link
    $html .= <<LI;
<div class="ref-group">
    <div class="ref-index">$inc.</div>
    <div class="ref-body">
        <span class="ref-author"><a href="$p[8]">$p[0]</a></span>.
        <span class="ref-title">$p[1]</span>.
        <span class="ref-author">$p[2]</span>,
        <span class="ref-pub">$p[6]</span>.
    </div>
</div>
LI
    $inc++;
}


$inc--;
print "<h3>$inc Patents</h3>\n";
print $html;




