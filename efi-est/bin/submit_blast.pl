#!/usr/bin/env perl
use strict;
use Getopt::Long;

my $seq;
my $queue;
my $jobdir;
my $evalue;
my $nresults;
my $resultdir;
my $efimodule;
#for testing various stages, comment out prior backtick lines to keep from re-running jobs

my $result=GetOptions ("seq=s"           => \$seq,
		    "queue=s"		   => \$queue,
		    "jobdir=s"		   => \$jobdir,
		    "resultdir=s"	   => \$resultdir,
		    "evalue=s"		   => \$evalue,
		    "nresults=s"	   => \$nresults,
		    "efimodule=s"	   => \$efimodule);
		
unless(defined $seq){
  die "You must specify a blast sequence with -seq\n";
}
unless(defined $jobdir) {
  die "You must define a qsub script directory with -jobdir\n";
}
unless(defined $evalue) {
  die "You must define a evalue with -evalue\n";
}
unless(defined $nresults) {
  die "You must define number of results with -nresults\n";

}
unless(defined $resultdir) {
  die "You must define an output directory with -resultdir\n";
}
#qsub script to create blast job
my $QSUB;
open(QSUB,">$jobdir/blasthits.sh") or die "could not create blast submission script $jobdir/blasthits.sh\n";
print QSUB "#!/bin/bash\n";
print QSUB "#PBS -j oe\n";
print QSUB "#PBS -S /bin/bash\n";
print QSUB "#PBS -q $queue\n";
print QSUB "#PBS -l nodes=1:ppn=1\n";
print QSUB "#PBS -d $jobdir\n";
print QSUB "module load $efimodule\n";
print QSUB "pwd\n";
print QSUB "blasthits.pl -seq '$seq' -evalue $evalue -tmpdir $resultdir -nresults $nresults\n";
close QSUB;

my $importjob=`qsub $jobdir/blasthits.sh 2>&1`;
print "import job is:\n $importjob";
if ($?) {
	die("\n");
}
my @importjobline=split /\./, $importjob;


