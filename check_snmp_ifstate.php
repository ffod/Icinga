#!/usr/bin/php -f
<?php
###########################################
#
# Interfacecounter checkscript for nagios
# Written by Kai Zemke
# 18.08.2007
# Published under: Do whatever the fuck you like, but don't bother me licence
#
##########################################

$argCount=$argc;
$scriptName=$argv[0];
#$host=$argv[1];
#$community=$argv[2];
#$ifIndex=$argv[3];
#######
#0 for 32 bit counter / 1 for 64 bit counter
#######
#$countertype=$argv[4];
$debug="0";
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
#Oid fuer ifOperState: 1.3.6.1.2.1.2.2.1.8.[Index]
$ifOperStateOid="1.3.6.1.2.1.2.2.1.8.";
######
#Ifstate Array
$ifStateArray=array(
	'1' => 'up',
	'2' => 'down',
	'3' => 'testing',
	'4' => 'unknown',
	'5' => 'dormant',
	'6' => 'notPresent',
	'7' => 'lowerLayerDown',
);

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
		
		$ifState=snmp2_get($host,$community,$ifOperStateOid.$interfaceIndex);	
		
		if($ifState==1){
			echo "OK: Ifstate: ".$ifName." is ".$ifStateArray[$ifState]."\n";
			die(0);
		}
		elseif($ifState==2){
			echo "Critical: IfState: ".$ifName." is ".$ifStateArray[$ifState]."\n";
			die(2);
		}
		elseif($ifState==3||$ifState==4||$ifState==5||$ifState==6||$ifState==7){
			echo "Warning: IfState: ".$ifName." is ".$ifStateArray[$ifState]."\n";
                        die(1);	
		}
		else{
			echo "Unknown: Unknown state returned by device\n";
			die(3);
		}
	}
}
?>
