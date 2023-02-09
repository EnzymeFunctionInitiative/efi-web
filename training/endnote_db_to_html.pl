#!/bin/env perl

use strict;
use warnings;

use DBI;
use Getopt::Long;


my ($dbFile, $outputFile, $lineIndent);
my $result = GetOptions(
    "db-file=s"     => \$dbFile,
    "output=s"      => \$outputFile,
    "line-indent=n" => \$lineIndent,
);

die "Need valid --db-file" if not $dbFile or not -f $dbFile;
die "Need --output file" if not $outputFile;

$lineIndent = 0 if not $lineIndent;


my $dbh = DBI->connect("dbi:SQLite:dbname=$dbFile", "", "");
die "Failed to establish database connection" if not $dbh;


my $sql = "SELECT id, date, title, author, year, pages, volume, number, secondary_title, edition, publisher, place_published, url, electronic_resource_number FROM refs WHERE trash_state = 0 ORDER BY year, access_date";

my $sth = $dbh->prepare($sql);
$sth->execute;

my @data;
while (my $row = $sth->fetchrow_hashref) {
    my $date = parseDate($row);
    my $pub = parsePub($row);
    my $author = parseAuthor($row);
    my $url = parseUrl($row);
    push @data, {date => $date, pub => $pub, title => $row->{title}, author => $author, year => $row->{year}, volume => $row->{volume}, number => $row->{number}, pages => $row->{pages}, url => $url};
}



my @dataByDate = sort { $a->{date} cmp $b->{date} } @data;
my $numPubs = scalar @dataByDate;


open my $outfh, ">", $outputFile or die "Unable to write to output file $outputFile: $!";

$outfh->print("<h3>$numPubs Journal Articles</h3>\n");

$lineIndent = " " x $lineIndent;

my $inc = 1;
foreach my $data (@dataByDate) {
    my $html = <<HTML;
<div class="ref-group">
    <div class="ref-index">$inc.</div>
    <div class="ref-body">
        <span class="ref-author">$data->{author}</span>,
        <span class="ref-title">$data->{title}</span>.
        <span class="ref-pub">$data->{pub}</span>,
        <span class="ref-year">$data->{year}</span>.
HTML
    if ($data->{volume}) {
        $html .= "        <span class=\"ref-volume\">$data->{volume}</span>";
        $html .= "(<span class=\"ref-number\">$data->{number}</span>)" if $data->{number};
        $html .= ": p. <span class=\"ref-page\">$data->{pages}</span>" if $data->{pages};
        $html .= ".\n";
    }
    $html .= "        <span class=\"ref-url\"><a href=\"$data->{url}\">$data->{url}</a></span>\n" if $data->{url};
    $html .= <<HTML;
    </div>
</div>
HTML
    my @p = split(m/\n/s, $html);
    $html = $lineIndent . join("\n$lineIndent", @p) . "\n";
    $outfh->print($html);
    $inc++;
}

close $outfh;












sub parseUrl {
    my $row = shift;

    if ($row->{electronic_resource_number}) {
        return "http://doi.org/$row->{electronic_resource_number}";
    } elsif ($row->{url}) {
        return $row->{url};
    } else {
        return "";
    }
}


sub parseAuthor {
    my $row = shift;

    my $raw = $row->{author};

    # Authors
    my @rawAuthors = split(m/\r/, $raw);

    my @authors;

    # Make initials instead of full first names
    foreach my $a (@rawAuthors) {
        my @p = splitAuthor($a);
        for (my $i = 1; $i <= $#p; $i++) {
            if ($p[$i] =~ m/^([A-Z])[^\.]+\.?$/i) {
                $p[$i] = "$1.";
            } elsif ($p[$i] =~ m/^([A-Z])$/) {
                $p[$i] .= ".";
            }
        }
        my $au = join(" ", @p);
        $au =~ s/\+/ /g;
        push @authors, $au;
    }

    my $author = join(", ", @authors);

    return $author;
}
sub splitAuthor {
    my $a = shift;

    #$a =~ s/(el|Al)\-/$1+/gi;
    $a =~ s/\b(San|de|Los)\s+/$1+/gi;
    #$a =~ s/([a-z])\~([a-zA-Z])/$1+$2/gi;
    $a =~ s/van der /van+der+/g;
    #$a =~ s/^(\S+)\-([A-Z])/$1 - $2/g;

    my @p = split(m/\s+/, $a);

    return @p;
}


sub parsePub {
    my $row = shift;

    if ($row->{secondary_title}) {
        return $row->{secondary_title};
    } elsif ($row->{publisher}) {
        return $row->{publisher};
    } else {
        return "";
    }
}


sub parseDate {
    my $row = shift;

    if ($row->{edition} and $row->{edition} =~ m%(\d+)/(\d+)/(\d+)%) {
        return $row->{edition};
    }

    my %monthLookup = (
        "jan" => 1,
        "feb" => 2,
        "mar" => 3,
        "apr" => 4,
        "may" => 5,
        "jun" => 6,
        "jul" => 7,
        "aug" => 8,
        "sep" => 9,
        "oct" => 10,
        "nov" => 11,
        "dec" => 12,
    );

    my $month = "00";
    my $day = "00";
    if ($row->{date}) {
        $row->{date} =~ m/^(\w+)(\s+(\d+))?$/;
        if ($1) {
            $month = $monthLookup{lc $1};
        }
        if ($3) {
            $day = $3;
        }
    }

    # Manual fix...
    my $year = $row->{year} == 2922 ? 2022 : $row->{year};

    my $date = sprintf("%04d-%02d-%02d", $row->{year}, $month, $day);

    return $date;
}










__END__

CREATE TABLE refs(id INTEGER PRIMARY KEY AUTOINCREMENT
date TEXT NOT NULL DEFAULT ""
abstract TEXT NOT NULL DEFAULT ""
label TEXT NOT NULL DEFAULT ""
url TEXT NOT NULL DEFAULT ""
tertiary_title TEXT NOT NULL DEFAULT ""
tertiary_author TEXT NOT NULL DEFAULT ""
notes TEXT NOT NULL DEFAULT ""
isbn TEXT NOT NULL DEFAULT ""
custom_1 TEXT NOT NULL DEFAULT ""
custom_2 TEXT NOT NULL DEFAULT ""
custom_3 TEXT NOT NULL DEFAULT ""
custom_4 TEXT NOT NULL DEFAULT ""
alternate_title TEXT NOT NULL DEFAULT ""
accession_number TEXT NOT NULL DEFAULT ""
call_number TEXT NOT NULL DEFAULT ""
short_title TEXT NOT NULL DEFAULT ""
custom_5 TEXT NOT NULL DEFAULT ""
custom_6 TEXT NOT NULL DEFAULT ""
section TEXT NOT NULL DEFAULT ""
original_publication TEXT NOT NULL DEFAULT ""
reprint_edition TEXT NOT NULL DEFAULT ""
reviewed_item TEXT NOT NULL DEFAULT ""
author_address TEXT NOT NULL DEFAULT ""
caption TEXT NOT NULL DEFAULT ""
custom_7 TEXT NOT NULL DEFAULT ""
electronic_resource_number TEXT NOT NULL DEFAULT ""
translated_author TEXT NOT NULL DEFAULT ""
translated_title TEXT NOT NULL DEFAULT ""
name_of_database TEXT NOT NULL DEFAULT ""
database_provider TEXT NOT NULL DEFAULT ""
research_notes TEXT NOT NULL DEFAULT ""
language TEXT NOT NULL DEFAULT ""
access_date TEXT NOT NULL DEFAULT ""
last_modified_date TEXT NOT NULL DEFAULT ""
record_properties TEXT NOT NULL DEFAULT ""
added_to_library INTEGER NOT NULL DEFAULT 0
record_last_updated INTEGER NOT NULL DEFAULT 0
reserved3 INTEGER NOT NULL DEFAULT 0
fulltext_downloads TEXT NOT NULL DEFAULT ""
read_status TEXT NOT NULL DEFAULT ""
rating TEXT NOT NULL DEFAULT ""
reserved7 TEXT NOT NULL DEFAULT ""
reserved8 TEXT NOT NULL DEFAULT ""
reserved9 TEXT NOT NULL DEFAULT ""
reserved10 TEXT NOT NULL DEFAULT "");
