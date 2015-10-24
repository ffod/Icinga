#!/usr/bin/php -f
<?php
###########################################
#
# Interfacecounter checkscript for nagios/icinga
# Written by Kai Zemke
# 22.10.2015
#
##########################################
$argCount=$argc;
$scriptName=$argv[0];
#######
#Oid's
#######
#Oid fuer ifDescription: .1.3.6.1.2.1.2.2.1.2.[Index]
$ifDescOid=".1.3.6.1.2.1.2.2.1.2.";
#Oid fuer ifInOctets (32 bit counter): .1.3.6.1.2.1.2.2.1.10.[Index]
$ifInOctetsOid=".1.3.6.1.2.1.2.2.1.10.";
#Oid fuer ifOutOctets (32 bit counter): .1.3.6.1.2.1.2.2.1.16.[Index]
$ifOutOctetsOid=".1.3.6.1.2.1.2.2.1.16.";
#Oid fuer ifInDiscards: .1.3.6.1.2.1.2.2.1.13.[Index]
$ifInDiscardsOid=".1.3.6.1.2.1.2.2.1.13.";
#Oid fuer ifOutDiscards: .1.3.6.1.2.1.2.2.1.19.[Index]
$ifOutDiscardsOid=".1.3.6.1.2.1.2.2.1.19.";
#Oid fuer ifInErrors: .1.3.6.1.2.1.2.2.1.14.[Index]
$ifInErrorsOid=".1.3.6.1.2.1.2.2.1.14.";
#Oid fuer ifOutErrors: .1.3.6.1.2.1.2.2.1.20.[Index]
$ifOutErrorsOid=".1.3.6.1.2.1.2.2.1.20.";
#Oid fuer ifHCInOctets (64 bit counter): 1.3.6.1.2.1.31.1.1.1.6.[Index]
$ifHCInOctets="1.3.6.1.2.1.31.1.1.1.6.";
#Oid fuer ifHCOutOctets (64 bit counter): 1.3.6.1.2.1.31.1.1.1.10.[Index]
$ifHCOutOctets="1.3.6.1.2.1.31.1.1.1.10.";
#Oid fuer ifAlias: .1.3.6.1.2.1.31.1.1.1.18.[Index]
$ifAliasOid=".1.3.6.1.2.1.31.1.1.1.18.";
######

if ( $argCount < 4 ){
    echo "Error: Not enough options given \n\n";
    echo "basename '".$scriptName."' <host-ip> <community> <ifName>\n";
    exit(1);
}
else{
	$host=$argv[1];
	$community=$argv[2];
	$ifName=$argv[3];

	snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
	snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);

	$interfaceArray=snmp2_real_walk($host,$community,substr($ifDescOid,0,-1));
	$interfaceArrayFlipped=array_flip($interfaceArray);

	if(!array_key_exists($ifName,$interfaceArrayFlipped)){
		echo "Interface: ".$ifName." unbekannt\n";
		die();
	}
	else{
		$interfaceIndexFull=$interfaceArrayFlipped[$ifName];
		$interfaceIndex=end(explode('.', $interfaceIndexFull));
	
		$ifInBytes=snmp2_get($host,$community,$ifHCInOctets.$interfaceIndex);
		$ifOutBytes=snmp2_get($host,$community,$ifHCOutOctets.$interfaceIndex);
		$ifInDiscards=snmp2_get($host,$community,$ifInDiscardsOid.$interfaceIndex);
		$ifOutDiscards=snmp2_get($host,$community,$ifOutDiscardsOid.$interfaceIndex);
		$ifInErrors=snmp2_get($host,$community,$ifInErrorsOid.$interfaceIndex);
		$ifOutErrors=snmp2_get($host,$community,$ifOutErrorsOid.$interfaceIndex);
		$errors=$ifInErrors+$ifOutErrors;
	
		echo "Ok: IfDesc: ".$ifName." IfIndex: ".$interfaceIndex." | bitsIn=".$ifInBytes."c;;;; bitsOut=".$ifOutBytes."c;;;; discardsIn=".$ifInDiscards."c;;;; discardsOut=".$ifOutDiscards."c;;;; errors=".$errors."c;;;;\n";
	}
}
exit(0);
?>
