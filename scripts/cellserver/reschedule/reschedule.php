<?php

#leading spaces from beginning of the specification
$base_pattern ='/^\s*';
#look for eventual -disabled token
$base_pattern.='(?:\-(?P<disabled>disabled)\s+)?';
#look for -<mode> token
$base_pattern.='(?:\-(?P<mode>full|incr 1|trans)\s+';
#look for eventual -starting <day> <month> <year> token
$base_pattern.='(?:\-starting\s+(?P<starting>\d+ \d+ \d+)\s+)?';
#look for -every token
$base_pattern.='\-every\s+\-day\s+';
#given as either list of <weekdays>
$base_pattern.='(?:(?P<every>(?:(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun)\s+)*)|';
#or list of <days>
$base_pattern.='(?P<every_day>(?:\d+\s+)+)';
#and list of <months>
$base_pattern.='\-month\s+(?P<every_month>(?:(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+)+))';
#no clue why this is here, i keep it commented out
#$base_pattern.='|\-only\s+(?P<only_date>\d+ \d+ \d+)\s+';
#$base_pattern.='\-day\s+(?P<only_day>\d+)\s+';
#$base_pattern.='\-month\s+(?P<only_month>(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec))\s+)';
#look for eventual -exclude <days> token
$base_pattern.='(?:\-exclude\s+\-day\s+(?P<exclude_day>(?:\d+\s+)+))?';
#look for -at <hour>:<minute> token
$base_pattern.='\-at\s+(?P<at>\d+:\d+)';
#trailing spaces at end of the specification
$base_pattern.='\s*)/sim';

#beginning of the -exclude token
$exclude_pattern ='/';
#look for -<mode> token
$exclude_pattern.='(?:\-(?P<mode>full|incr 1|trans)\s+';
#look for -exclude -<day> token
$exclude_pattern.='\-exclude\s+\-day\s+(?P<day>\d+)\s+';
#look for -<month> token
$exclude_pattern.='\-month\s+(?P<month>Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+';
#look for -<year> token
$exclude_pattern.='\-year\s+(?P<year>\d+)\s+';
#look for -<at> token
$exclude_pattern.='\-at\s+(?P<at>..:..)';
#trailing spaces at end of the -exclude token
$exclude_pattern.='\s*)/sim';

#beginning of the -only token
$only_pattern ='/';
#look for -<mode> token
$only_pattern.='(?:\-(?P<mode>full|incr 1|trans)\s+';
#look for -only -<year> token
$only_pattern.='\-only\s+(?P<year>\d+)\s+';
#look for -<day> token
$only_pattern.='\-day\s+(?P<day>\d+)\s+';
#look for -<month> token
$only_pattern.='\-month\s+(?P<month>Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+';
#look for -<at> token
$only_pattern.='\-at\s+(?P<at>\d+:\d+)';
#trailing spaces at end of the -only token
$only_pattern.='\s*)/sim';

function cleanup_schedule($folder,$file,
                $exclude_day=0,$exclude_month='',$exclude_year=0,$time_pattern='.*',
                $reschedule_day=0,$reschedule_month='',$reschedule_year=0) {

        global $rescheduled,$excluded,$main,$base_pattern,$exclude_pattern,$only_pattern;
        if (!file_exists($folder . DIRECTORY_SEPARATOR . $file)) {
                echo sprintf("ERROR02: %s does not exist.\n",$file);
                return false;
        }
        $contents=trim(file_get_contents($folder . DIRECTORY_SEPARATOR . $file));
        if ($contents=='') {
                echo sprintf("WARN01: %s is empty.\n",$file);
                return false;
        }
        $exclude=0;
        $reschedule=0;
        $cleaned="";
        $exclude_file="";
        $modified=false;
        if ($exclude_day>0) {
                if (is_numeric($exclude_month)) $exclude_month=date('M',mktime(0,0,0,$exclude_month,1));
                if ($exclude_month=='') $exclude_month=date('M');
                if ($exclude_year==0) $exclude_year=date('Y');
                $exclude=strtotime(sprintf('%s %s %s', $exclude_day, $exclude_month, $exclude_year));
                $exclude_weekday=date('D',$exclude);
                if ($reschedule_day>0) {
                        if (is_numeric($reschedule_month)) $reschedule_month=date('M',mktime(0,0,0,$reschedule_month,1));
                        if ($reschedule_month=='') $reschedule_month=$exclude_month;
                        if ($reschedule_year==0) $reschedule_year=$exclude_year;
                        $reschedule=strtotime(sprintf('%s %s %s', $reschedule_day, $reschedule_month, $reschedule_year));
                }
        }
        preg_match_all($base_pattern,$contents,$match);
        $mode='';
        $every='';
        $at='';
        for ($i=0; $i<count($match['mode']); $i++) {
                $mode=trim($match['mode'][$i]);
                $every=trim($match['every'][$i]);
                $at=trim($match['at'][$i]);
                $cleaned.=($match['disabled'][$i]=='') ? "" : "-disabled\n";
                $cleaned.=sprintf("-%s\n", trim($match['mode'][$i]));
                $cleaned.=($match['starting'][$i]=='') ? "" : sprintf("-starting %s\n", trim($match['starting'][$i]));
                $cleaned.="-every\n";
                $cleaned.=($match['every'][$i]=='') ?
                        sprintf("\t-day %s\n\t-month %s\n", trim($match['every_day'][$i]), trim($match['every'][$i])) :
                        sprintf("\t-day %s\n", trim($match['every'][$i]));
                $cleaned.=sprintf("\t-at %s\n\n", trim($match['at'][$i]));
        }
        preg_match_all($only_pattern,$contents,$match);
        if (($reschedule>0) and stristr($every,$exclude_weekday) and preg_match(sprintf('/^%s:/',$time_pattern),$at)) {
                $modified=true;
                echo sprintf("OK03: %s will be rescheduled to %s %s %s at %s.\n",
                        $file,$reschedule_day,$reschedule_month,$reschedule_year,$at);
                $match['mode'][]=$mode;
                $match['day'][]=$reschedule_day;
                $match['month'][]=$reschedule_month;
                $match['year'][]=$reschedule_year;
                $match['at'][]=$at;
                $rescheduled++;
                preg_match(sprintf('/%s/i',$main['reschedule']),$file,$match1);
                $exclude_file=str_replace($match1[0],$main['instead'],$file);
        }
        for ($i=0; $i<count($match['mode']); $i++) {
                if ((strtotime(sprintf('%s %s %s', $match['day'][$i], $match['month'][$i], $match['year'][$i]))==$exclude)
                        and preg_match(sprintf('/^%s:/',$time_pattern),$at)) continue;
                if (($main['cleanup']==1) and (strtotime(sprintf('%s %s %s %s',
                        $match['day'][$i], $match['month'][$i], $match['year'][$i], $match['at'][$i]))<strtotime('now')))
                        continue;
                $new=sprintf("-%s\n-only %s\n\t-day %s -month %s\n\t-at %s\n\n",
                        $match['mode'][$i], $match['year'][$i], $match['day'][$i], $match['month'][$i], $match['at'][$i]);
                $cleaned.=(stristr($cleaned,$new)) ? "" : $new;
        }
        preg_match_all($exclude_pattern,$contents,$match);
        if (($exclude>0) and stristr($every,$exclude_weekday) and preg_match(sprintf('/^%s:/',$time_pattern),$at)) {
                $modified=true;
                echo sprintf("OK04: %s will be excluded from %s %s %s at %s.\n",$file,$exclude_day,$exclude_month,$exclude_year,$at);
                $match['mode'][]=$mode;
                $match['day'][]=$exclude_day;
                $match['month'][]=$exclude_month;
                $match['year'][]=$exclude_year;
                $match['at'][]='--:--';
                $excluded++;
        }
        for ($i=0; $i<count($match['mode']); $i++) {
                if ((strtotime(sprintf('%s %s %s',
                        $match['day'][$i], $match['month'][$i], $match['year'][$i]))==$reschedule)) continue;
                if (($main['cleanup']==1) and (strtotime(sprintf('%s %s %s %s',
                        $match['day'][$i], $match['month'][$i], $match['year'][$i], $at))<strtotime('now'))) continue;
                $new=sprintf("-%s\n-exclude\n\t-day %s -month %s -year %s\n\t-at %s\n\n",
                        $match['mode'][$i], $match['day'][$i], $match['month'][$i], $match['year'][$i], $match['at'][$i]);
                $cleaned.=(stristr($cleaned,$new)) ? "" : $new;
        }
        if (!$modified and ($main['cleanup']==1)) echo sprintf("OK05: %s will be just cleaned up.\n", $file);
        if ($cleaned=='') echo sprintf("WARN02: %s is empty after cleanup.\n",$file);
        if (($modified or ($main['cleanup']==1)) and ($main['test']<>1)) {
                $backup=str_replace($main['omni_server'],$main['backup'],$folder);
                if (isset($main['backup']) and !is_file($backup . DIRECTORY_SEPARATOR . $file)) {
                        if (!is_dir($backup)) mkdir($backup,0644,true);
                        if (!copy($folder . DIRECTORY_SEPARATOR . $file,$backup . DIRECTORY_SEPARATOR . $file))
                                echo sprintf("ERROR05: cannot copy %s to %s.\n", $folder . DIRECTORY_SEPARATOR . $file, $backup);
                }
                $fp = fopen($folder . DIRECTORY_SEPARATOR . $file, 'w');
                if (!fwrite ($fp, $cleaned . PHP_EOL))
                        echo sprintf("ERROR04: cannot write to %s.\n", $folder . DIRECTORY_SEPARATOR . $file);
                fclose ($fp);
                chmod($folder . DIRECTORY_SEPARATOR . $file, 0644);
        }
        if ($exclude_file!=='') {
                echo sprintf("OK06: %s respective '%s' will be processed.\n",$file, $main['instead']);
                cleanup_schedule($folder,$exclude_file,$reschedule_day,$reschedule_month,$reschedule_year);
        }
        return true;
}

date_default_timezone_set( 'UTC' );

$ini                    = parse_ini_file('reschedule.ini', true);
$main                   = array_change_key_case($ini,CASE_LOWER);
$main                   = array_change_key_case($main['main'],CASE_LOWER);
$folders                = glob($main['omni_server'] . DIRECTORY_SEPARATOR . 'barschedules' . DIRECTORY_SEPARATOR . '*' , GLOB_ONLYDIR);
$folders[]              = $main['omni_server'] . DIRECTORY_SEPARATOR . 'schedules';

$excluded               = 0;
$ignored                = 0;
$rescheduled    = 0;

echo "OK01: RESCHEDULING STARTED.\n";

foreach ($ini as $key => $value) {
        $entry = array_change_key_case($value,CASE_LOWER);
        if (isset($entry['omni_server'])) continue;
        $error='';
        if (!isset($entry['mtw_day'])) $error='"mtw_day" entry is missing'. PHP_EOL;
        if (!isset($entry['time_pattern'])) $error='"time_pattern" entry is missing'. PHP_EOL;
        if (!isset($entry['new_day'])) $error='"new_day" entry is missing'. PHP_EOL;
        list($day,$month,$year)=explode(' ',$entry['mtw_day']);
        if ($day<1 or $day>31) $error=sprintf('"%s" day is incorrect',$day). PHP_EOL;
        if (!stristr('(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)',$month)) $error=sprintf('"%s" month is incorrect',$month). PHP_EOL;
        if ($year<2013 or $year>2100) $error=sprintf('"%s" year is incorrect',$year). PHP_EOL;
        $time_pattern=$entry['time_pattern'];
        list($new_day,$new_month,$new_year)=explode(' ',$entry['new_day']);
        if ($new_day<1 or $new_day>31) $error=sprintf('"%s" day is incorrect',$new_day). PHP_EOL;
        if (!stristr('(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)',$new_month)) $error=sprintf('"%s" month is incorrect',$new_month). PHP_EOL;
        if ($new_year<2013 or $new_year>2100) $error=sprintf('"%s" year is incorrect',$new_year). PHP_EOL;
        if ($error!=='') die(sprintf('ERROR01: $s in [%s] entry',$error,$key));
}

$i=0;

foreach ($folders as $folder) {
        $files=glob($folder . DIRECTORY_SEPARATOR . '*');
        sort($files);
        foreach ($files as $file) {
                $i++;
                $schedule=basename($file);
                if (preg_match(sprintf('/%s/i',$main['ignore']),$schedule)) {
                        echo sprintf("OK02: %s will be ignored.\n",$schedule);
                        $ignored++;
                        continue;
                }
                foreach ($ini as $key => $value) {
                        if ($key=='main') continue;
                        $entry = array_change_key_case($value,CASE_LOWER);
                        list($exclude_day,$exclude_month,$exclude_year)=explode(' ',$entry['mtw_day']);
                        $time_pattern=$entry['time_pattern'];
                        list($reschedule_day,$reschedule_month,$reschedule_year)=explode(' ',$entry['new_day']);
                        if(preg_match(sprintf('/%s/i',$main['reschedule']),$schedule)) {
                                cleanup_schedule($folder,$schedule,$exclude_day,$exclude_month,$exclude_year,$time_pattern,$reschedule_day,$reschedule_month,$reschedule_year);
                                continue;
                        }
                        if (preg_match(sprintf('/%s/i',$main['exclude']),$schedule)) {
                                cleanup_schedule($folder,$schedule,$exclude_day,$exclude_month,$exclude_year,$time_pattern);
                                continue;
                        }
                        if ($main['cleanup']==1) cleanup_schedule($folder,$schedule);
                }
        }
}
echo sprintf("OK99: RESCHEDULING FINISHED. %s schedules processed, %s ignored, %s excluded, %s rescheduled.\n", $i, $ignored, $excluded, $rescheduled);
?>

