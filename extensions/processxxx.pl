#!/usr/bin/env perl
my %dict;

for my $dictfn (@ARGV) {
    open( INP, '<', $dictfn ) or die "Cannot open dict $dictfn";
    while( <INP> ) {
        chomp;
        if( /^([^=]*)=([^\r]*)\r?$/ ) {
            $dict{$1}=$2;
        }
    }
    close INP;
}

open( FILELIST, '<', 'xxxfiles.txt') or die "Cannot open xxxfiles.txt";
for my $fn( <FILELIST> ) {
    $fn =~ s/\r?\n$//;

    open( INP, '<', $fn . '.xxx' ) or die "Cannot open $fn.xxx";
    open OUT, '>', $fn;
    while( <INP> ) {
        if( /^(.*)\#\#(.*)\#\#(.*)$/ ) {
            exists($dict{$2}) or die "Undefined name $2 on file $fn";
            print OUT $1.$dict{$2}.$3."\n";
        } else {
            print OUT;
        }
    }
    close OUT;
    close INP;
}
close FILELIST;

exit(0);
