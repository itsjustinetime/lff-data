<?php
function nth_day_of_month($nbr, $day, $mon, $year){ 
   $date = mktime(0, 0, 0, $mon, 0, $year);

   if($date == 0){ 
      user_error(__FUNCTION__."(): Invalid month or year", E_USER_WARNING); 
      return(FALSE); 
   } 

   $day = ucfirst(strtolower($day));

   if(!in_array($day, array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'))){ 
      user_error(__FUNCTION__."(): Invalid day", E_USER_WARNING); 
      return(FALSE); 
   }

   for($week = 1; $week <= $nbr; $week++){ 
      $date = strtotime("next $day", $date); 
   }

   return($date); 
} 

    class pluginLFFData extends Plugin {
		public function parse($content) {
			$content=str_replace('[lffmap]', pluginLFFData::LFF_Map(), $content);
			$content=str_replace('[lffevents]', pluginLFFData::LFF_Events(), $content);
			$content=str_replace('[lffeventsgrid]', pluginLFFData::LFF_EventsGrid(), $content);
			$content=str_replace('[lffservices]', pluginLFFData::LFF_Services(), $content);
			$content=str_replace('[lffvenues]', pluginLFFData::LFF_VenuesRec(), $content);
			$content=str_replace('[lffhotels]', pluginLFFData::LFF_Hotels(), $content);
			$content=str_replace('[lfffuture]', pluginLFFData::LFF_Future(), $content);
			$content=str_replace('[lffhighlights]', pluginLFFData::LFF_Highlights(), $content);
			if (str_contains($content,'[youtube',)) { $content=pluginLFFData::YoutubeVid($content); }
			if (str_contains($content,'[lffvideo',)) { $content= pluginLFFData::LFF_Video($content); }
			
			return $content;
		}
	
		public function init() {
		 $this->customHooks = array('LFF_Map','LFF_Events','LFF_EventsGrid','LFF_Offers','LFF_Venues','LFF_VenuesRec','LFF_Services','LFF_Header','LFF_Footer','LFF_Future', 'LFF_Hotels');	
		}
		
		public function YoutubeVid($thecontent) {
			//echo "YOUTUBE";
			$tempcontent=$thecontent;
			$tempcontent=preg_replace('/\[youtube\=(.*?)\]/','',$tempcontent);
			$html=$tempcontent;
			//$html.='<div class="ytembed">';
			//$html .='<iframe src="https://www.youtube.com/embed/'. $youtubeId .'" allowfullscreen></iframe>';
			//$html .='</div>';
			
			$html=$tempcontent;
			foreach(preg_split("/((\r?\n)|(\r\n?))/", $thecontent) as $line){
				if (str_contains($line,"[youtube="))
					{ 
						$youtubeId=preg_split('/\[youtube\=(.*?)/',$line)[1];
						$youtubeId=strip_tags(str_replace(']','',$youtubeId));
						//echo 'YT Id:'.$youtubeId;
						//$html="";
						$html.='<div class="ytembed">';
						$html .='<iframe src="https://www.youtube.com/embed/'. $youtubeId .'" allowfullscreen class="ytvideo"></iframe>';
						$html .='</div>';
					}
			}
			return $html;
		}
		
		public function LFF_Video($thecontent) {
			$tempcontent=$thecontent;
			$tempcontent=preg_replace('/\[lffvideo\=(.*?)\]/','',$tempcontent);
			$html=$tempcontent;
			foreach(preg_split("/((\r?\n)|(\r\n?))/", $thecontent) as $line){
				if (str_contains($line,"[lffvideo="))
					{ 
						$filename=preg_split('/\[lffvideo\=(.*?)/',$line)[1];
						$filename=strip_tags(str_replace(']','',$filename));
						//echo 'filename:'.$filename;
						$html .= '<div class="videocontainer">';
						$html .= '<video class="pagevideo" autoplay muted controls>';
						$html .= '<source src="'.$filename.'" type="video/mp4">';
						$html .= 'Your browser does not support the video tag for '.$filename;
						$html .= '</video>';
						$html .= '</div>';
					}
			}
			return $html;
		}
		
		public function LFF_Future() {
			$count = 1;
			$t = time();
			$month = (int)date('m');
			$day = date('d');
			$year = (int)date('Y');
			$html = '<div class="gallery">';
		    $html .= '<h4>Upcoming Leeds First Friday Dates</h4>';
			$html .= '<ul class="lffdategrid">';
			for ($i=1; $i<15; $i++) {
				if ($count>12) {break;}
				$lffdatenum = nth_day_of_month(1,'Friday',$month,$year);
				if ( $lffdatenum > $t) {
					$lffmonth = date('F',$lffdatenum);
					$lffdate=date('jS M Y',$lffdatenum);
					$html .= '<li class="lffdate"><div class="lffdatebg"><img class="lffdatebg" src="'.HTML_PATH_ROOT.'/bl-themes/jttheme/img/lff_bg1.webp" /></div><div class="lffdatetitle"><img class="lffdatelogo" src="'.HTML_PATH_ROOT.'/bl-themes/jttheme/img/lfflogowhite.webp" />'.$lffmonth.'</div>';
					$html .= '<div class="lffdatelabel">'.$lffdate.'</div>';
					$html .= '</li>';
					$count++;
				}
				$month++;
				if ($month > 12) { $month=1; $year++;}	
			}	
			$html .= '</ul>';
			$html .= '</div>';
			return $html;
		}
		
		public function LFF_Map() {
			$path = PATH_CONTENT.'lff-events/maps/';
			$result = [];
			foreach (glob($path.'/*.{webp,jpg,jpeg,gif,bmp,png}',GLOB_BRACE) as $file) {
				$result[] = [filemtime($file), $file];
			}
			rsort($result);
			$filename=explode("/",$result[0][1]);
			$latestImage=end($filename);
			$html='<div class="lffmap">';
			$html .= '<img class="lffmapimg" src="'.HTML_PATH_ROOT.'bl-content/lff-events/maps/'.$latestImage.'" />';
			$html .= '</div>';
			return $html;
		}
		
        public function LFF_Events() {
			$html='<div class="lffeventslider">
			<div class="slidertitle"><div class="slidertitletext">LFF Events</div>
			<div class="sliderbtns">
			<div class="lffprevbtn slidernavbtn">&lt;</div>
			<div class="lffnextbtn slidernavbtn">&gt;</div>
			</div>
			</div>
			
			<!-- Slider main container -->
			<div class="swiper mySwiper">
				<div class="swiper-wrapper">
            		<div class="swiper-slide firstslide"></div>
			<!-- Slides -->';
			//$myfile = file_get_contents("https://www.leedsfirstfriday.com/web/app/lffeventdata.json", "r") or die("Unable to load LFF data :'(");
			$myfile = file_get_contents(PATH_CONTENT."/lff-events/json/lffeventdata.json", "r") or die("Unable to load LFF data :'(");
			$myData=json_decode($myfile,true);
			$eventList=$myData['events'];
			//var_dump($myData['events']);
			$eventCount=1;
			$myTime=strtotime(date("Y-m-d h:i"));
			foreach ($eventList as $event) {		
				if (strtotime($event['eventstart']) < $myTime) { continue; }
				//if ($eventCount > 6) {break; }		
				$html .='<div class="swiper-slide">';
				$html .= '	<div class="lffevent">';
				$html .= '		<div class="lffeventimage">';
				$html .= '			<img class="eventimg" src="'.HTML_PATH_ROOT.'/bl-content/lff-events/images/'.$event['eventimage'].'"/>';
				$html .= '		</div>';
				$html .= '		<div class="eventtext">';
				$html .= '			<div class="lffeventtitle">';
				$html .= '				'.$event['eventtitle'];
				$html .= '			</div>';
				$html .= '			<div class="eventDate">';
				$evStart = new DateTime($event['eventstart']);
				$evEnd = new DateTime($event['eventend']);
				$evDay = $evStart->format('D jS M Y');
				$html .= $evDay."</div>";
				$html .= '			<div class="eventtime">';
				$html .= '				'.$evStart->format('ga')." - " .$evEnd->format('ga');
				$html .= '			</div>';
				$html .= '			<div class="eventVenue">';
				$html .= '				'.$event['eventvenue'];
				$html .= '			</div>';
				$html .= '			<div class="eventSubtitle">';
				$html .= '				'.$event['eventsubtitle'];
				$html .= '			</div>';
				$html .= '		</div>';
				$html .= '	</div>';
				$html .= '</div>';
				$eventCount++;
			}
			$html .= '		</div>';
			$html .= '	</div>';
			$html .= '</div>';
			return $html;
		}
		
		public function LFF_EventsGrid() {
			$html = '<div class="gallery2">';
			$myfile = file_get_contents(PATH_CONTENT.'/lff-events/json/lffeventdata.json', "r") or die("Unable to load LFF data :'(");
			$myData=json_decode($myfile,true);
			$eventList=$myData['events'];
			//var_dump($myData['events']);
			$eventCount=1;
			$myTime=strtotime(date("Y-m-d h:i"));
			foreach ($eventList as $event) {		
				if (strtotime($event['eventstart']) < $myTime) { continue; }
				if ($eventCount > 6) {break; }		
				$html .= '<div class="galleryitem">';
				$html .= '	<div class="gridevent">';
				$html .= '		<div class="lffeventimage" style="view-transition-name: eventimage'.$event['eventid'].';">';
				$html .= '         <a href="about?event='.str_replace(" ","_",$event['eventtitle']).'">';
				$html .= '			<img class="galleryimg" src="'.HTML_PATH_ROOT.'/bl-content/lff-events/images/'.$event['eventimage'].'"/>';
				$html .= '         </a>';
				$html .= '		</div>';
				$html .= '		<div class="gridtext">';
				$html .= '			<div class="gridinnertext gridtexttitle">';
				$html .= '				'.$event['eventtitle'];
				$html .= '			</div>';
				$html .= '			<div class="gridinnertext">';
				$evStart = new DateTime($event['eventstart']);
				$evEnd = new DateTime($event['eventend']);
				$evDay = $evStart->format('D jS M Y');
				$html .= $evDay.'</div>';
				$html .= '			<div class="eventtime">';
				$html .= '<i class="fa-solid fa-clock"></i>				'.$evStart->format('ga')." - " .$evEnd->format('ga');
				$html .= '			</div>';
				$html .= '			<div class="gridinnertext">';
				$html .= '   <i class="fa-solid fa-location-dot"></i>				'.$event['eventvenue'];
				$html .= '			</div>';
				//$html .= '			<div class="gridinnertext">';
				//$html .= '				'.$event['eventsubtitle'];
				//$html .= '			</div>';
				$html .= '		</div>';
				$html .= '	</div>';
				$html .= '</div>';
				$eventCount++;
			}
			$html .= '		</div>';
			return $html;
		}
		
		public function LFF_Highlights() {
			$myfile = file_get_contents(PATH_CONTENT.'/lff-events/json/lffeventdata.json', "r") or die("Unable to load LFF data :'(");
			$myData=json_decode($myfile,true);
			$highlightList=$myData['highlights'];
			$html='<section>';
			$html .= '<h3>LFF Highlights</h3>';
			$html .= '<div class="gallery">';
			foreach ($highlightList as $highlight) {
				$html .= '<div class="galleryitem">';
				$html .= '    <div class="gridevent">';
				$html .= '         <a href="highlight.php?highlight='.$highlight['highlightid'].'">';
				$html .= '        <img class="galleryimg" src="'.HTML_PATH_ROOT.'/bl-content/lff-events/images/'.$highlight['venueimage'].'" />';
				$html .= '         </a>';
				$html .= '         <div class="gridinnertext">';
				$html .= '             <div class="venue">';
				$html .= '             '.$highlight['highlightvenue'];
				$html .= '         </div>';
				$html .= '         <div class="title" style="font-size:0.75em;">'.$highlight['highlighttitle'].'</div>';
				$html .= '         </div>';
				$html .= '    </div>';
				$html .= '</div>';
			
			}
			$html .= '</div>';
			$html .= '</section>';
			return $html;
		}
		public function LFF_Hotels() {
			$myfile = file_get_contents(PATH_CONTENT.'/lff-events/json/lffeventdata.json', "r") or die("Unable to load LFF data :'(");
			$myData=json_decode($myfile,true);
			$venueList=$myData['places'];	
			$html = '<section>';
		if ($WHERE_AM_I == "home" ) $html .= '<h4>Hotels</h4>';
			$html .= '<div class="gallery">';
			foreach ($venueList as $venue) {
				if ($venue['venuecategory'] != "Hotels" || $venue['venuerecommended'] != "on") { continue; }
				$html .= '<div class="galleryitem" style="view-transition-name: venueimage'.$venue['venueid'].';">';
				$html .= '<div class="gridevent">';
				$html .= '         <a href="about?venue='.str_replace(' ','_',$venue['venuename']).'">';
				$html .= '	<img  class="galleryimg" src="'.HTML_PATH_ROOT.'/bl-content/lff-events/images/'.$venue['venueimage'].'" />';
				$html .= ' </a>';
				$html .= '	<div class="gallerytile">';
				$html .= '		'.$venue['venuename'];
				$html .= '	</div>';
				$html .= '	</div>';
				$html .= '</div>';	
			}
			$html .= '</div>';
			return $html;
		}
		
		public function LFF_Venues() {
			$myfile = file_get_contents(PATH_CONTENT.'/lff-events/json/lffeventdata.json', "r") or die("Unable to load LFF data :'(");
			$myData=json_decode($myfile,true);
			$venueList=$myData['places'];	
			$html = '<section>';
			$html .= '<h3>Places to visit</h3>';
			foreach ($venueList as $venue) {
				$html .= '<div class="lffvenue">';
				$html .= '         <a href="about?venue='.$venue['venuename'].'">';
				$html .= '	<img style="view-transition-name:venueimager'.$venue['venueid'].';" src="'.HTML_PATH_ROOT.'/bl-content/lff-events/images/'.$venue['venueimage'].'" />';
				$html .= ' </a>';
				$html .= '	<div class="venuename">';
				$html .= '		'.$venue['venuename'];
				$html .= '	</div>';
				$html .= '</div>';
			}
			$html .= '</section>';
			return $html;
		}
		
		public function LFF_VenuesRec() {
			$myfile = file_get_contents("https://www.leedsfirstfriday.com/web/bl-content/lff-events/json/lffeventdata.json", "r") or die("Unable to load LFF data :'(");
			$myData=json_decode($myfile,true);
			$venueList=$myData['places'];	
			
			$html = '';
			$html .= '<h4>Recommended Venues</h4>';
			$html .= '<div class="gallery">';
			foreach ($venueList as $venue) {
				if ($venue['venuerecommended']==0 || $venue['venuecategory'] == "Hotels") continue;
				$html .= '<div class="galleryitem" style="view-transition-name: venueimage'.$venue['venueid'].';">';
				$html .= '<div class="gridevent">';
				$html .= '         <a href="about?venue='.str_replace(" ","_",$venue['venuename']).'">';
				$html .= '	<img  class="galleryimg" src="'.HTML_PATH_ROOT.'/bl-content/lff-events/images/'.$venue['venueimage'].'" />';
				$html .= ' </a>';
				$html .= '	<div class="gallerytile">';
				$html .= '		'.$venue['venuename'];
				$html .= '	</div>';
				$html .= '	</div>';
				$html .= '</div>';	
			}
			$html .= '</div>';
			return $html;
		}
		
		public function LFF_Services() {
			$myfile = file_get_contents("https://www.leedsfirstfriday.com/web/bl-content/lff-events/json/lffeventdata.json", "r") or die("Unable to load LFF data :'(");
			$myData=json_decode($myfile,true);
			$serviceList=$myData['services'];
			$html = '<div class="gallery2">';		
			foreach ($serviceList as $service) {
				$html .= '<div class="galleryitem">';
				$html .= '	<div class="gridevent">';
				$html .= '		<div class="lffeventimage">';
				$html .= '			<img class="galleryimg" src="'.HTML_PATH_ROOT.'/bl-content/lff-events/images/'.$service['serviceimage'].'" />';
				$html .= '		</div>';
				$html .= '      <div class="gridtext">';
				//$html .= '			<div class="stitle">';
				$html .= '				'.$service['servicename'];
				//$html .= '			</div>';
				$html .= '			<ul class="serviceicons">';
				if ($service['servicefacebook']) { $html .= '<li class="serviceicon"><a href="'.$service['servicefacebook'].'"><i class="fa-brands fa-square-facebook"></i></a></li>'; }
				if ($service['serviceinstagram']) { $html .= '<li class="serviceicon"><a href="'.$service['serviceinstagram'].'"><i class="fa-brands fa-square-instagram"></i></a></li>'; }
				if ($service['servicewebsite']) { $html .= '<li class="serviceicon"><a href="'.$service['servicewebsite'].'"><i class="fa-solid fa-link"></i></a></li>'; }
				$html .= '';
				$html .= '			</ul>';
				$html .= '		</div>';
				$html .= '	</div>';
				$html .= '</div>';		
			}
			$html .= '</div>';
			return $html;
		}
		
		/* public function beforeSiteLoad() {
			global $content; // Better with instance() no?
					
		       // Foreach loaded page, modify the page's content
			foreach ($content as $key=>$page) {
				// Get the page content
				$pageContent = $page->contentRaw();

				// Search and replace the string
				//$events=LFF_Events();
				$newPageContent = pluginLFFData::parse($pageContent);

				// Set the new page content
				$page->setField('content', $newPageContent);
			}
		}
		*/
		public function beforeSiteLoad() {
			global $Page, $post, $Posts,$WHERE_AM_I,$content;
			/*
			switch($WHERE_AM_I) 		{
			case 'post':
				$content = $post->contentRaw();				
		        // Parse Shortcodes
		        $content = pluginLFFData::parse( $content );
		        $Post->setField('content', $content, true);	
				break;
			case 'page':
				$content = $page->contentRaw();		
		        // Parse Shortcodes
		        $content = pluginLFFData::parse( $content );
		        $Page->setField('content', $content, true);
				break;
				
			default:
				// 
			*/
			foreach($content as $key=>$Post)
			{
				// Full content parsed by Parsedown
				$mycontent = $Post->contentRaw();
		
				// Parse with Shortcode
				$mycontent = pluginLFFData::parse( $mycontent );
		
				// Set full content
				$Post->setField('content', $mycontent, true);
		
				// Set page break content
				$explode = explode(PAGE_BREAK, $mycontent);
				$Post->setField('breakContent', $explode[0], true);
				$Post->setField('readMore', !empty($explode[1]), true);
			}  		
		}
    }
?>