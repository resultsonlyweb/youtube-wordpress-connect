<?php
/*
Plugin Name: Youtube Wordpress Connect
Plugin URI: http://bigjmedia.com/development/youtube_wordpress_connect
Description: Displays a showcase spot with lists below either all videos or sectioned into playlists.
Version: 0.1
Author: Josh Johnson - Founder/CEO - Big J Media
*/


// Action hook to create the shortcode
add_shortcode('ytwpconnect', 'ytwpconnect');


function ytwpconnect($atts) {
	
    extract(shortcode_atts(array(
		"channel" => 'Default', 
		"limit" => '10', 
        "thumbnail_width" => '150',
        "show_titles" => '1',
        "showcase" => '',
        "showcase_width" => "600", 
        "showcase_height" => "300"), $atts));
	
	// Get Fancy Box Loaded
    echo '<script type="text/javascript" src="http://bigjmedia.com/fancybox/jquery.fancybox-1.3.4.js"></script>';
    echo '<link rel="stylesheet" type="text/css" href="http://bigjmedia.com/fancybox/jquery.fancybox-1.3.4.css" media="screen" />';
	echo '<div class="clear"> </div>';

    //Get the list of Play lists that goes with the channel provided in the short code
    $str = 'https://gdata.youtube.com/feeds/api/users/ballroomdancenj/playlists?v=1&key=AI39si4CfIhK1opoEOFxSfVCBoA1-fKQuEt-1HCWHqTRHNXbmW3UxYoyD0qn8QQh_HKvkZHDKV1ksq532u8P5NKY3p0Drkm0bg';
    $arr = xml2array($str);

	// if nothing is returned from the channel display this error
	if (empty($arr)) { 
		echo('Youtube Channel "' . $channel . '" not found');
	}
	
	$showcase = '';
    $showcase_output = ''; //string to contain showcase content
	
	echo '<h1>Below is a Selection of Media from Ballroom Dance NJ</h1>
	<h2><span style="color: #ff0000;">Interview Video</span></h2>
	Congratulations to Joan Wright for being the World Champion!

	<iframe src="http://www.youtube.com/embed/WjAzO2vr6S0?rel=0" frameborder="0" width="640" height="360"></iframe>
	<h2><span style="color: #ff0000;">Showcase Tango - Sergei &amp; Anna Altshuller - 11/05/2011</span>
	Artist/Song: Mireille Mathieu Paris Un
	<iframe src="http://www.youtube.com/embed/IvyGFdXbuvc?rel=0" frameborder="0" width="640" height="360"></iframe></h2>';
	
	if ($showcase != "") {
			$iframe_src = 'http://www.youtube.com/v/' . $showcase . '?version=3&f=videos&app=youtube_gdata&autoplay=1';
	    	$showcase_output = "<iframe height='" . $showcase_height . "' width='". $showcase_width . "' src='" . $iframe_src . "'></iframe>";
			echo $showcase_output;
		};
		
	//Pull the play list links from the php arrays that return from the xml to array function
    	$feed = $arr['feed'];
    	$entries = $feed['entry'];
		$i = 0;
		$p_arr = array();
		$playlist_count = $feed['openSearch:totalResults'];
		while($i < $playlist_count) {
			$playlist_title = $feed['entry'][$i]['title'];
			echo '<h2>' . $playlist_title . '</h2>';
			$playlist_url = $feed['entry'][$i]['gd:feedLink_attr']['href'];
			$p_arr = xml2array($playlist_url);
			$p_feed = $p_arr['feed'];
			$p_entries = $p_feed['entry'];

		    $entries_output = ''; //string to contain entries

			//print_r($p_entries);

		   	//create output: all videos in channel as thumbnails and showcase if requested
		    foreach ($p_entries as $p_entry):

		      $thumbnail = ($p_entry['media:group']['media:thumbnail']['0_attr']['url']);
		      $title = ($p_entry['media:group']['media:title']);

		      $entries_output .= "<div class='youtubechannelEntry " . $i . "'>";

		      $full_path = $p_entry['link']['0_attr']['href'];
		      $entries_output .= '<a href="'. $full_path . '" title="" id="yt">'; 
		      $entries_output .= "<img src='" . $thumbnail . "' width='" . $thumbnail_width . "'/>";
		      $entries_output .= "</a>";
		
		      if ($show_titles == '1'):
		        $entries_output .= "<span class='youtube_title'>" . $title . "</span><br>";
		      endif;
		
		      $entries_output .= "</div>";

		    endforeach;
			$i++;
			

		
		echo $entries_output;
		}
		
		//echo '<script type="text/javascript"> '.  //Doesn't currently work since the this.href.replace function can't be in this echo setup
				'$("#yt").live("click", function() {'.
				'$.fancybox({'.
				'"padding"        : 0, "autoScale"      : false,"transitionIn"   : "none","transitionOut"  : "none","title" : this.title,"width" : 680,"height"         : 495,"href" : this.href.replace(new RegExp("watch\\?v=", "i"), "v/"),"type" : "swf","swf" : {"wmode" : "transparent", "allowfullscreen"    : "true"}'.
				'});'.
				'return false;'.
				'});'.
				'</script>';
			            
}


//getting php array from xml feed
function xml2array($url, $get_attributes = 1, $priority = 'tag')
{
    $contents = "";
    if (!function_exists('xml_parser_create'))
    {
        return array ();
    }
    $parser = xml_parser_create('');
    if (!($fp = @ fopen($url, 'rb')))
    {
        return array ();
    }
    while (!feof($fp))
    {
        $contents .= fread($fp, 8192);
    }
    fclose($fp);
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($contents), $xml_values);
    xml_parser_free($parser);
    if (!$xml_values)
        return; //Hmm...
    $xml_array = array ();
    $parents = array ();
    $opened_tags = array ();
    $arr = array ();
    $current = & $xml_array;
    $repeated_tag_index = array (); 
    foreach ($xml_values as $data)
    {
        unset ($attributes, $value);
        extract($data);
        $result = array ();
        $attributes_data = array ();
        if (isset ($value))
        {
            if ($priority == 'tag')
                $result = $value;
            else
                $result['value'] = $value;
        }
        if (isset ($attributes) and $get_attributes)
        {
            foreach ($attributes as $attr => $val)
            {
                if ($priority == 'tag')
                    $attributes_data[$attr] = $val;
                else
                    $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
            }
        }
        if ($type == "open")
        { 
            $parent[$level -1] = & $current;
            if (!is_array($current) or (!in_array($tag, array_keys($current))))
            {
                $current[$tag] = $result;
                if ($attributes_data)
                    $current[$tag . '_attr'] = $attributes_data;
                $repeated_tag_index[$tag . '_' . $level] = 1;
                $current = & $current[$tag];
            }
            else
            {
                if (isset ($current[$tag][0]))
                {
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    $repeated_tag_index[$tag . '_' . $level]++;
                }
                else
                { 
                    $current[$tag] = array (
                        $current[$tag],
                        $result
                    ); 
                    $repeated_tag_index[$tag . '_' . $level] = 2;
                    if (isset ($current[$tag . '_attr']))
                    {
                        $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                        unset ($current[$tag . '_attr']);
                    }
                }
                $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                $current = & $current[$tag][$last_item_index];
            }
        }
        elseif ($type == "complete")
        {
            if (!isset ($current[$tag]))
            {
                $current[$tag] = $result;
                $repeated_tag_index[$tag . '_' . $level] = 1;
                if ($priority == 'tag' and $attributes_data)
                    $current[$tag . '_attr'] = $attributes_data;
            }
            else
            {
                if (isset ($current[$tag][0]) and is_array($current[$tag]))
                {
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    if ($priority == 'tag' and $get_attributes and $attributes_data)
                    {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag . '_' . $level]++;
                }
                else
                {
                    $current[$tag] = array (
                        $current[$tag],
                        $result
                    ); 
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $get_attributes)
                    {
                        if (isset ($current[$tag . '_attr']))
                        { 
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset ($current[$tag . '_attr']);
                        }
                        if ($attributes_data)
                        {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                    }
                    $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                }
            }
        }
        elseif ($type == 'close')
        {
            $current = & $parent[$level -1];
        }
    }
    return ($xml_array);
}
?>
