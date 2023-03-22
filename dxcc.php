<?php
# dxcc class - determining the DXCC country of a callsign
# 
# Copyright (C) 2023 by LA8AJA in 2023
#
# Based on original works by DJ5CW (DJ1YFK)
#
class dxcc {
    protected $dxcc = array();      // array with main prefix -> (CQZ, ITUZ, ...) 
    protected $fullcalls = array(); // array with full calls (=DL1XYZ)
    protected $prefixes = array();  // array with main prefix -> (all, prefixes,..)

    public function __construct() {
        $this->read_cty();
    }

    function validatecallsign($callsign, $mycallsign) {
        if (preg_match('/[0-9A-Za-z\/]/', $callsign)) {
            $result = $this->dxcc($callsign);
            $dist = '';
            if ($mycallsign != '' && preg_match('/[0-9A-Za-z\/]/', $mycallsign)) {
                $mycallresult = $this->dxcc($mycallsign);
                $dist = $this->qrbqtf($mycallresult[4], $mycallresult[5], $result[4], $result[5]);
            }
            $this->printresult($result, $dist, $callsign, $mycallsign);
        } else {
            echo "Not a valid call";
        }
    }

    public function calltester($callsign) {
        return $this->dxcc($callsign);
    }

    function qrbqtf($mylat, $mylon, $hislat, $hislon) {
        $PI = 3.14159265;
        $z = 180 / $PI;

        $g = acos(sin($mylat / $z) * sin($hislat / $z) + cos($mylat / $z) * cos($hislat / $z) * cos(($hislon - $mylon) / $z));

        $dist = $g * 6371;
        $dir = 0;

        if ($dist != 0) {
            $dir = acos((sin($hislat / $z) - sin($mylat / $z) * cos($g)) / (cos($mylat / $z)  * sin($g))) * 360 / (2 * $PI);
        }

        if (sin(($hislon - $mylon) / $z) < 0) {
            $dir = 360 - $dir;
        }
        $dir = 360 - $dir;

        $result[0] = $dist;
        $result[1] = $dir;

        return $result;
    }

    function printresult($dxccinfo, $dist, $callsign, $mycallsign) {
        ?>
        <table class="styled-table">
            <thead>
            <tr>
                <th>Callsign</th>
                <?php if ($mycallsign) echo '<th>Mycallsign</th>'; ?>
                <th>Country Name</th>
                <th>Prefix</th>
                <th>CQ Zone</th>
                <th>ITU Zone</th>
                <th>Continent</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>UTC shift</th>
                <?php if ($mycallsign) echo '<th>Distance (km)</th>'; ?>
                <?php if ($mycallsign) echo '<th>Bearing (°)</th>'; ?>
            </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo strtoupper($callsign); ?></td>
                    <?php if ($mycallsign) echo '<td>' . strtoupper($mycallsign) . '</td>'; ?>
                    <td><?php echo empty($dxccinfo[0]) ? 'None' : $dxccinfo[0]; ?></td>
                    <td><?php echo empty($dxccinfo[7]) ? 'None' : $dxccinfo[7]; ?></td>
                    <td><?php echo empty($dxccinfo[1]) ? '' : $dxccinfo[1]; ?></td>
                    <td><?php echo empty($dxccinfo[2]) ? '' : $dxccinfo[2]; ?></td>
                    <td><?php echo empty($dxccinfo[3]) ? '' : $dxccinfo[3]; ?></td>
                    <td><?php echo empty($dxccinfo[4]) ? '' : $dxccinfo[4]; ?></td>
                    <td><?php echo empty($dxccinfo[5]) ? '' : $dxccinfo[5]; ?></td>
                    <td><?php echo empty($dxccinfo[6]) ? '' : $dxccinfo[6]; ?></td>
                    <?php if ($mycallsign) echo '<td>' . round($dist[0], 0) . '</td>'; ?>
                    <?php if ($mycallsign) echo '<td>' . round($dist[1], 0) . '</td>'; ?>
                </tr>
            </tbody>
        </table>
        <?php
    }

    function dxcc($testcall) {
        $matchchars = 0;
        $matchprefix = '';
        $zones = '';                   # annoying zone exceptions
        $goodzone = '';
        $letter = '';
        $csadditions = '/^P$|^R$|^A$|^M$|^LH$|^SK$/';
    
        $testcall = strtoupper($testcall);
    
        if (in_array($testcall, $this->fullcalls)) {                    # direct match with "="
                                                                        # do nothing! don't try to resolve WPX, it's a full
                                                                        # call and will match correctly even if it contains a /
        } elseif (preg_match('/(^OH\/)|(\/OH[1-9]?$)/', $testcall)) {   # non-Aland prefix!
            $testcall = "OH";                                           # make callsign OH = finland
        } elseif (preg_match('/(^3D2R)|(^3D2.+\/R)/', $testcall)) {     # seems to be from Rotuma
            $testcall = "3D2RR";                                        # will match with Rotuma
        } elseif (preg_match('/^3D2C/', $testcall)) {                   # seems to be from Conway Reef
            $testcall = "3D2CR";                                        # will match with Conway
        } elseif (preg_match('/(^LZ\/)|(\/LZ[1-9]?$)/', $testcall)) {   # LZ/ is LZ0 by DXCC but this is VP8h
            $testcall = "LZ";
        } elseif (preg_match('/(^KG4)[A-Z09]{3}/', $testcall)) {        # KG4/ and KG4 5 char calls are Guantanamo Bay. If 6 char, it is USA
            $testcall = "K";
        } elseif (preg_match('/(^KG4)[A-Z09]{2}/', $testcall)) {        # KG4/ and KG4 5 char calls are Guantanamo Bay. If 6 char, it is USA
            $testcall = "KG4";
        } elseif (preg_match('/(^KG4)[A-Z09]{1}/', $testcall)) {        # KG4/ and KG4 5 char calls are Guantanamo Bay. If 6 char, it is USA
            $testcall = "K";
        } elseif (preg_match('/\w\/\w/', $testcall)) {                  # check if the callsign has a "/"
            if (preg_match_all('/^((\d|[A-Z])+\/)?((\d|[A-Z]){3,})(\/(\d|[A-Z])+)?(\/(\d|[A-Z])+)?$/', $testcall, $matches)) {
                $prefix = $matches[1][0];
                $callsign = $matches[3][0];
                $suffix = $matches[5][0];
                
                if ($prefix) {
                    $prefix = substr($prefix, 0, -1); # Remove the / at the end 
                }
                if ($suffix) {
                    $suffix = substr($suffix, 1); # Remove the / at the beginning
                };
                if (preg_match($csadditions, $suffix)) {
                    if ($prefix) {
                        $testcall = $prefix;  
                    } else {
                        $testcall = $callsign;
                    }
                } else {
                    $testcall = $this->wpx($testcall, 1);                              
                    if ($testcall == '') {
                        return '';
                    }
                    
                    $testcall = $testcall . "AA";                             # use the wpx prefix instead, which may
                                                                                # intentionally be wrong, see &wpx!
                }
            }
        }
    
        $letter = substr($testcall, 0, 1);
    
        foreach ($this->prefixes as $mainprefix => $value) {                  # Runs through the DXCC list
            foreach ($this->prefixes[$mainprefix] as $test) {
    
                $len = strlen($test);
                if ($letter != substr($test, 0, 1)) {                   # Continues if no match, will speed up things
                    continue;
                }
                $zones = '';
    
                if (($len > 5) && ((strpos($test, '(') !== false) || (strpos($test, '[') !== false))) { # extra zones
                    preg_match('/^([A-Z0-9\/]+)([\[\(].+)/', $test, $matches);
                    if (isset($matches[2])) {
                        $zones .= $matches[2];
                    }
                    $len = strlen($matches[1]);
                }
    
                if ((substr($testcall, 0, $len) == substr($test, 0, $len)) && ($matchchars <= $len)) {
                    $matchchars = $len;
                    $matchprefix = $mainprefix;
                    $goodzone = $zones;
                }
            }
        }
    
        $mydxcc = array();                                        # save typing work
    
        if (array_key_exists($matchprefix, $this->dxcc)) {
            $mydxcc = $this->dxcc[$matchprefix];
        } else {
            $mydxcc = array_fill(1, 6, 0);
            $mydxcc[0] = 'Unknown';
        }
    
        # Different zones?
        if ($goodzone) {
            if (preg_match('/\((\d+)\)/', $goodzone, $matches)) { # CQ-Zone in ()
                $mydxcc[1] = $matches[1];
            }
            if (preg_match('/\[(\d+)\]/', $goodzone, $matches)) { # ITU-Zone in []
                $mydxcc[2] = $matches[1];
            }
        }
    
        # cty.dat has special entries for WAE countries which are not separate DXCC
        # countries. Those start with a "*", for example *TA1. Those have to be changed
        # to the proper DXCC. Since there are opnly a few of them, it is hardcoded in
        # here.
    
        if (preg_match('/^\* /', $testcall)) { # WAE country!
            if ($mydxcc[7] == '*TA1') {
                $mydxcc[7] = "TA";
            }        # Turkey
            if ($mydxcc[7] == '*4U1V') {
                $mydxcc[7] = "OE";
            }    # 4U1VIC is in OE..
            if ($mydxcc[7] == '*GM/s') {
                $mydxcc[7] = "GM";
            }    # Shetlands
            if ($mydxcc[7] == '*IG9') {
                $mydxcc[7] = "I";
            }        # African Italy
            if ($mydxcc[7] == '*IT9') {
                $mydxcc[7] = "I";
            }        # Sicily
            if ($mydxcc[7] == '*JW/b') {
                $mydxcc[7] = "JW";
            }    # Bear Island
        }
    
        return $mydxcc;
    }

    ###############################################################################
#
# &wpx derives the Prefix following WPX rules from a call. These can be found
# at: http://www.cq-amateur-radio.com/wpxrules.html
#  e.g. DJ1YFK/TF3  can be counted as both DJ1 or TF3, but this sub does 
# not ask for that, always TF3 (= the attached prefix) is returned. If that is 
# not want the OP wanted, it can still be modified manually.
#
# Addendum by LA8AJA.
# Here are some calls to test in each case
# OH/DJ1YFK -> since we are adding a 0, it could be interpreted as OH0, that's why we set it as OH - Finland
# N6TR/7 -> USA
# KH0CW -> USA (if everything is not ok, this was Mariana Island)
# A45XR/0 -> Oman
# RV0AL/0/P -> Asiatic Russia
# DJ1YFK/VE1 -> Canada
# DJ1YFK/QRP -> Germany
# DJ1YFK/LGT -> Germany
# RAEM -> Asiatic Russia
# HD1QRC90 -> Ecuador
###############################################################################

function wpx($testcall, $i) {
    $prefix = '';
    $a = '';
    $b = '';
    $c = '';

    $lidadditions = '/^QRP|^LGT/';
    $csadditions = '/^P$|^R$|^A$|^M$|^LH$|^SK$/';
    $noneadditions = '/^MM$|^AM$/';

    # First check if the call is in the proper format, A/B/C where A and C
    # are optional (prefix of guest country and P, MM, AM etc) and B is the
    # callsign. Only letters, figures and "/" is accepted, no further check if the
    # callsign "makes sense".
    # 23.Apr.06: Added another "/X" to the regex, for calls like RV0AL/0/P
    # as used by RDA-DXpeditions....

    if (preg_match_all('/^((\d|[A-Z])+\/)?((\d|[A-Z]){3,})(\/(\d|[A-Z])+)?(\/(\d|[A-Z])+)?$/', $testcall, $matches)) {

        # Now $1 holds A (incl /), $3 holds the callsign B and $5 has C
        # We save them to $a, $b and $c respectively to ensure they won't get 
        # lost in further Regex evaluations.
        $a = $matches[1][0];
        $b = $matches[3][0];
        $c = $matches[5][0];

        if ($a) {
            $a = substr($a, 0, -1); # Remove the / at the end 
        }
        if ($c) {
            $c = substr($c, 1); # Remove the / at the beginning
        };

        # In some cases when there is no part A but B and C, and C is longer than 2
        # letters, it happens that $a and $b get the values that $b and $c should
        # have. This often happens with liddish callsign-additions like /QRP and
        # /LGT, but also with calls like DJ1YFK/KP5. ~/.yfklog has a line called    
        # "lidadditions", which has QRP and LGT as defaults. This sorts out half of
        # the problem, but not calls like DJ1YFK/KH5. This is tested in a second
        # try: $a looks like a call (.\d[A-Z]) and $b doesn't (.\d), they are
        # swapped. This still does not properly handle calls like DJ1YFK/KH7K where
        # only the OP's experience says that it's DJ1YFK on KH7K.
        if (!$c && $a && $b) {                          # $a and $b exist, no $c
            if (preg_match($lidadditions, $b)) {        # check if $b is a lid-addition
                $b = $a;
                $a = null;                              # $a goes to $b, delete lid-add
            } elseif ((preg_match('/\d[A-Z]+$/', $a)) && (preg_match('/\d$/', $b))) {   # check for call in $a
                $temp = $b;
                $b = $a;
                $a = $temp;
            }
        }

        # *** Added later ***  The check didn't make sure that the callsign
        # contains a letter. there are letter-only callsigns like RAEM, but not
        # figure-only calls. 

        if (preg_match('/^[0-9]+$/', $b)) {            # Callsign only consists of numbers. Bad!
            return null;            # exit, undef
        }

        # Depending on these values we have to determine the prefix.
        # Following cases are possible:
        #
        # 1.    $a and $c undef --> only callsign, subcases
        # 1.1   $b contains a number -> everything from start to number
        # 1.2   $b contains no number -> first two letters plus 0 
        # 2.    $a undef, subcases:
        # 2.1   $c is only a number -> $a with changed number
        # 2.2   $c is /P,/M,/MM,/AM -> 1. 
        # 2.3   $c is something else and will be interpreted as a Prefix
        # 3.    $a is defined, will be taken as PFX, regardless of $c 

        if (($a == null) && ($c == null)) {                     # Case 1
            if (preg_match('/\d/', $b)) {                       # Case 1.1, contains number
                preg_match('/(.+\d)[A-Z]*/', $b, $matches);     # Prefix is all but the last
                $prefix = $matches[1];                          # Letters
            } else {                                            # Case 1.2, no number 
                $prefix = substr($b, 0, 2) . "0";               # first two + 0
            }
        } elseif (($a == null) && (isset($c))) {                # Case 2, CALL/X
            if (preg_match('/^(\d)/', $c)) {                    # Case 2.1, number
                preg_match('/(.+\d)[A-Z]*/', $b, $matches);     # regular Prefix in $1
                # Here we need to find out how many digits there are in the
                # prefix, because for example A45XR/0 is A40. If there are 2
                # numbers, the first is not deleted. If course in exotic cases
                # like N66A/7 -> N7 this brings the wrong result of N67, but I
                # think that's rather irrelevant cos such calls rarely appear
                # and if they do, it's very unlikely for them to have a number
                # attached.   You can still edit it by hand anyway..  
                if (preg_match('/^([A-Z]\d)\d$/', $matches[1])) {        # e.g. A45   $c = 0
                    $prefix = $matches[1] . $c;  # ->   A40
                } else {                         # Otherwise cut all numbers
                    preg_match('/(.*[A-Z])\d+/', $matches[1], $match); # Prefix w/o number in $1
                    $prefix = $match[1] . $c; # Add attached number   
                }
                //var_dump($prefix);
            } elseif (preg_match($csadditions, $c)) {
                preg_match('/(.+\d)[A-Z]*/', $b, $matches);     # Known attachment -> like Case 1.1
                $prefix = $matches[1];
            } elseif (preg_match($noneadditions, $c)) {
               return '';
             } elseif (preg_match('/^\d\d+$/', $c)) {            # more than 2 numbers -> ignore
                preg_match('/(.+\d)[A-Z]* /', $b, $matches);    # see above
                $prefix = $matches[1][0];
            } else {                                            # Must be a Prefix!
                if (preg_match('/\d$/', $c)) {                  # ends in number -> good prefix
                    $prefix = $c;
                } else {                                        # Add Zero at the end
                    $prefix = $c . "0";
                }
            }
        } elseif (($a) && (preg_match($noneadditions, $c))) {                # Case 2.1, X/CALL/X ie TF/DL2NWK/MM - DXCC none
            return '';
        } elseif ($a) {
            # $a contains the prefix we want
            if (preg_match('/\d$/', $a)) {                      # ends in number -> good prefix
                $prefix = $a;
            } else {                                            # add zero if no number
                $prefix = $a . "0";
            }
        }
        # In very rare cases (right now I can only think of KH5K and KH7K and FRxG/T
        # etc), the prefix is wrong, for example KH5K/DJ1YFK would be KH5K0. In this
        # case, the superfluous part will be cropped. Since this, however, changes the
        # DXCC of the prefix, this will NOT happen when invoked from with an
        # extra parameter $_[1]; this will happen when invoking it from &dxcc.

        if (preg_match('/(\w+\d)[A-Z]+\d/', $prefix, $matches) && $i == null) {
            $prefix = $matches[1][0];
        }
        return $prefix;
    } else {
        return '';
    }       # no proper callsign received.*/
}           # wpx ends here

 
    /*
    * Read cty.dat from AD1C
    */
    function read_cty() {
        $file = 'cty.dat';
        $mainprefix = '';

        if (is_readable($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES);

            foreach ($lines as $line) {
                if (substr($line, 0, 1) !== ' ') {            # New DXCC
                    if (preg_match('/\s+([*A-Za-z0-9\/]+):+$/', $line, $matches)) {
                        $mainprefix = $matches[1];
                        $this->dxcc[$mainprefix] = preg_split('/(\s*,*\s*)*:+(\s*,*\s*)*/', $line);
                    }
                } else {                                        # prefix-line

                    # read full calls into separate array. This array only
                    # contains the information that this is a full call

                    $line = trim($line);
                    if (preg_match_all('/=([A-Z0-9\/]+)(\(\d+\))?(\[\d+\])?[,;]/', $line, $matches)) {
                        foreach ($matches[1] as $match) {
                            $this->fullcalls[] = $match;
                        }
                    }

                    # Continue with everything else. Including full calls, which will
                    # be read as normal prefixes.

                    $calls = explode(',', $line);
                    foreach ($calls as $call) {
                        if (strlen($call) > 0) {
                            $this->prefixes[$mainprefix][] = str_replace(';', '', str_replace('=', '', $call));
                        }
                    }
                }
            }
        }
    }
}