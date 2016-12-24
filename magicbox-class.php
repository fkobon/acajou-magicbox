<?php 
    /*
    *   MagicBox Class
    *   @package magicbox 
    */

class MagicBox {
    
    public function __construct() {
        // add filters and actions according to OOP patterns.
        
        add_filter( 'wp_nav_menu_objects', array($this,'add_menu_parent_class') );
        add_filter('the_title', array($this,'custom_the_title'), 10, 2);
        add_action( 'admin_action_duplicate_post_as_draft', array($this,'duplicate_post_as_draft') );
        add_filter('post_row_actions', array($this,'duplicate_post_link'), 10, 2); // add the feature to the dashboard post rows
        add_filter('page_row_actions', array($this,'duplicate_post_link'), 10, 2); // add the feature to the dashboard page rows

    }
    
    //get data out of an upload image
    public function get_attachment_id_from_src ($image_src) {
        global $wpdb;
        $query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";
        $id = $wpdb->get_var($query);
        return $id;
    }
    // get attachement
    public function wp_get_attachment( $attachment_id ) {

        $attachment = get_post( $attachment_id );
        return array(
            'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
            'caption' => $attachment->post_excerpt,
            'description' => $attachment->post_content,
            'href' => get_permalink( $attachment->ID ),
            'src' => $attachment->guid,
            'title' => $attachment->post_title
        );
    }
    
    // print template directory absolute url
    public function wp_url() {
        bloginfo('template_directory');
    }
    
    // custom the_title
    public function custom_the_title($title, $id) {
        // return $title;
        return str_replace("&#13;","",$title);
        return str_replace("&rsquo;","",$title);
        return str_replace("&#146;","",$title);
        return str_replace("’","",$title);
    }

    //special public function for displaying categories if a post
    public function categories() {


    $categories = get_the_category();
    $separator = '/';
    $output = '';
    $output .='<div class="tag-box">';

    if($categories){
        foreach($categories as $category) {
        $output .= '<a href="'.get_category_link( $category->term_id ).'" title="' . esc_attr( sprintf( __( "View all posts in %s" ), $category->name ) ) . '">'.$category->cat_name.'</a> ';

    }
    // echo trim($output, $separator);
    }
    $output .='</div><!--tag-box-->';
    echo $output;
    }


    //custom page or archive title
    public function custom_title() {

      $showOnHome = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show
      $delimiter = '/'; // delimiter between crumbs
      $home = 'Accueil'; // text for the 'Home' link
      $showCurrent = 1; // 1 - show current post/page title in breadcrumbs, 0 - don't show
      $before = '<span class="current-page">'; // tag before the current crumb
      $after = '</span>'; // tag after the current crumb

      global $post;
      $homeLink = get_bloginfo('url');

      if (is_home() || is_front_page()) {

        if ($showOnHome == 1) echo '<div class="span12">
        <div class="breadcrumb clearfix"> <span></span><a href="' . $homeLink . '">' . $home . '</a></div></div>';

      } else {

        // echo '<div class="span12"><span></span><a href="' . $homeLink . '">' . $home . '</a> ' . $delimiter . ' ';

        if ( is_category() ) {
          $thisCat = get_category(get_query_var('cat'), false);
          if ($thisCat->parent != 0) echo get_category_parents($thisCat->parent, TRUE, ' ' . $delimiter . ' ');
          echo $before . '' . single_cat_title('', false) . '' . $after;

        } elseif ( is_search() ) {
          echo $before . 'Résultats pour "' . get_search_query() . '"' . $after;

        } elseif ( is_day() ) {
          echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
          echo '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $delimiter . ' ';
          echo $before . get_the_time('d') . $after;

        } elseif ( is_month() ) {
          echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
          echo $before . get_the_time('F') . $after;

        } elseif ( is_year() ) {
          echo $before . get_the_time('Y') . $after;

        } elseif ( is_single() && !is_attachment() ) {
          if ( get_post_type() != 'post' ) {
            $post_type = get_post_type_object(get_post_type());
            $slug = $post_type->rewrite;
            // echo '<a href="' . $homeLink . '/' . $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a>';
            if ($showCurrent == 1) echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
          } else {
            $cat = get_the_category(); $cat = $cat[0];
            $cats = get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
            if ($showCurrent == 0) $cats = preg_replace("#^(.+)\s$delimiter\s$#", "$1", $cats);
            echo $cats;
            if ($showCurrent == 1) echo $before . get_the_title() . $after;
          }

        } elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {
          $post_type = get_post_type_object(get_post_type());
          echo $before . $post_type->labels->singular_name . $after;

        } elseif ( is_attachment() ) {
          $parent = get_post($post->post_parent);
          $cat = get_the_category($parent->ID); $cat = $cat[0];
          echo get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
          echo '<a href="' . get_permalink($parent) . '">' . $parent->post_title . '</a>';
          if ($showCurrent == 1) echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;

        } elseif ( is_page() && !$post->post_parent ) {
          if ($showCurrent == 1) echo $before . get_the_title() . $after;

        } elseif ( is_page() && $post->post_parent ) {
          $parent_id  = $post->post_parent;
          $breadcrumbs = array();
          while ($parent_id) {
            $page = get_page($parent_id);
            $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
            $parent_id  = $page->post_parent;
          }
          $breadcrumbs = array_reverse($breadcrumbs);
          for ($i = 0; $i < count($breadcrumbs); $i++) {
            echo $breadcrumbs[$i];
            if ($i != count($breadcrumbs)-1) echo ' ' . $delimiter . ' ';
          }
          if ($showCurrent == 1) echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;

        } elseif ( is_tag() ) {
          echo $before . 'Tag "' . single_tag_title('', false) . '"' . $after;

        } elseif ( is_author() ) {
           global $author;
          $userdata = get_userdata($author);
          echo $before . 'Articles de ' . $userdata->display_name . $after;

        } elseif ( is_404() ) {
          echo $before . '404' . $after;
        }

        if ( get_query_var('paged') ) {
          if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ' (';
          echo __('Page') . ' ' . get_query_var('paged');
          if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ')';
        }

        // echo '</div>';

      }
    } // end custom_title()
    // breadcrumb
    public function qt_custom_breadcrumbs() {

      $showOnHome = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show
      $delimiter = ' / '; // delimiter between crumbs
      $home = 'Accueil'; // text for the 'Home' link
      $showCurrent = 1; // 1 - show current post/page title in breadcrumbs, 0 - don't show
      $before = '<span class="current-page">'; // tag before the current crumb
      $after = '</span>'; // tag after the current crumb

      global $post;
      $homeLink = get_bloginfo('url');

      if (is_home() || is_front_page()) {

        if ($showOnHome == 1) echo '<div class="span12">
        <div class="breadcrumb clearfix"> <span></span><a href="' . $homeLink . '">' . $home . '</a></div></div>';

      } else {

        echo '<div class="span12"><span></span><a href="' . $homeLink . '">' . $home . '</a> ' . $delimiter . ' ';

        if ( is_category() ) {
          $thisCat = get_category(get_query_var('cat'), false);
          if ($thisCat->parent != 0) echo get_category_parents($thisCat->parent, TRUE, ' ' . $delimiter . ' ');
          echo $before . 'Articles de la catégorie "' . single_cat_title('', false) . '"' . $after;

        } elseif ( is_search() ) {
          echo $before . 'Résultats pour "' . get_search_query() . '"' . $after;

        } elseif ( is_day() ) {
          echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
          echo '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $delimiter . ' ';
          echo $before . get_the_time('d') . $after;

        } elseif ( is_month() ) {
          echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
          echo $before . get_the_time('F') . $after;

        } elseif ( is_year() ) {
          echo $before . get_the_time('Y') . $after;

        } elseif ( is_single() && !is_attachment() ) {
          if ( get_post_type() != 'post' ) {
            // $post_type = get_post_type_object(get_post_type());
            // $slug = $post_type->rewrite;
            // echo '<a href="' . $homeLink . $delimiter . $slug['slug'] . $delimiter.'>' . $post_type->labels->singular_name . '</a>';
            // if ($showCurrent == 1) echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
          } else {
            $cat = get_the_category(); $cat = $cat[0];
            $cats = get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
            if ($showCurrent == 0) $cats = preg_replace("#^(.+)\s$delimiter\s$#", "$1", $cats);
            echo $cats;
            if ($showCurrent == 1) echo $before . get_the_title() . $after;
          }

        } elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {
          $post_type = get_post_type_object(get_post_type());
          echo $before . $post_type->labels->singular_name . $after;

        } elseif ( is_attachment() ) {
          $parent = get_post($post->post_parent);
          $cat = get_the_category($parent->ID); $cat = $cat[0];
          echo get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
          echo '<a href="' . get_permalink($parent) . '">' . $parent->post_title . '</a>';
          if ($showCurrent == 1) echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;

        } elseif ( is_page() && !$post->post_parent ) {
          if ($showCurrent == 1) echo $before . get_the_title() . $after;

        } elseif ( is_page() && $post->post_parent ) {
          $parent_id  = $post->post_parent;
          $breadcrumbs = array();
          while ($parent_id) {
            $page = get_page($parent_id);
            $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
            $parent_id  = $page->post_parent;
          }
          $breadcrumbs = array_reverse($breadcrumbs);
          for ($i = 0; $i < count($breadcrumbs); $i++) {
            echo $breadcrumbs[$i];
            if ($i != count($breadcrumbs)-1) echo ' ' . $delimiter . ' ';
          }
          if ($showCurrent == 1) echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;

        } elseif ( is_tag() ) {
          echo $before . 'Posts tagged "' . single_tag_title('', false) . '"' . $after;

        } elseif ( is_author() ) {
           global $author;
          $userdata = get_userdata($author);
          echo $before . 'Articles posted by ' . $userdata->display_name . $after;

        } elseif ( is_404() ) {
          echo $before . 'Error 404' . $after;
        }

        if ( get_query_var('paged') ) {
          if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ' (';
          echo __('Page') . ' ' . get_query_var('paged');
          if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ')';
        }

        echo '</div>';

      }
    } 
    

    // retreives image from the post
    public function getImage($num) {
        global $more;
        $more = 1;
        $content = get_the_content();
        $count = substr_count($content, '<img');
        $start = 0;
        for($i=1;$i<=$count;$i++) {
        $imgBeg = strpos($content, '<img', $start);
        $post = substr($content, $imgBeg);
        $imgEnd = strpos($post, '>');
        $postOutput = substr($post, 0, $imgEnd+1);
        $image[$i] = $postOutput;
        $start=$imgEnd+1;  

        $cleanF = strpos($image[$num],'src="')+5;
        $cleanB = strpos($image[$num],'"',$cleanF)-$cleanF;
        $imgThumb = substr($image[$num],$cleanF,$cleanB);

        }
        if(stristr($image[$num],'<img')) { return $imgThumb; }
        $more = 0;
    }

    //Dynamic image automatically resized
    public function img_url($w,$h,$display=false) {
        $width = $w;                                                                  // Optional. Defaults to '150'
        $height = $h;                                                                 // Optional. Defaults to '150'
        $crop = true;                                                                  // Optional. Defaults to 'true'
        $retina = false;
        $url = wp_get_attachment_url( get_post_thumbnail_id($post->ID));
        if(""==$url):
        $url = getImage(1);
        endif;
                                                                       // Optional. Defaults to 'false'
        // Call the resizing public function (returns an array)
        $image = matthewruddy_image_resize( $url, $width, $height, $crop, $retina );
        //print_r($image);
        if(is_array($image)){
            if($display){
            echo $image['url'];    
            }else {
                return $image['url'];
            }
        }
    }
    
    // revamp the excerpt function
    public function new_excerpt($max_char, $more_link_text = '...',$notagp = false, $stripteaser = 0, $more_file = '') {
            $content = get_the_content($more_link_text, $stripteaser, $more_file);
            $content = apply_filters('the_content', $content);
            $content = str_replace(']]>', ']]&gt;', $content);
            $content = strip_tags($content);
            if (""==$content) {
            $content = "Non a vel turpis tincidunt rhoncus magna mattis! Integer ac, lacus, elit. Et ac est cursus, etiam mus adipiscing auctor, elit vel mid mattis! Pid facilisis! Tincidunt. Lorem dictumst dapibus, tincidunt placerat vel dolor rhoncus rhoncus mid velit massa. Scelerisque! Porttitor placerat auctor a, turpis adipiscing et magna eros pulvinar aliquam aliquam enim pulvinar cum lorem tempor pulvinar cum. Dolor, a magnis, ultrices dis, tincidunt sed, adipiscing vel ridiculus. In augue tristique";
            }

           if (isset($_GET['p']) && strlen($_GET['p']) > 0) {
              if($notagp) {
              echo $content;
              }
              else {
              // echo '<div class="slide_excerpt">';
              echo $content;
              // echo "</div>";
              }
           }
           else if ((strlen($content)>$max_char) && ($espacio = strpos($content, " ", $max_char ))) {
                $content = substr($content, 0, $espacio);
                $content = $content;
                if($notagp) {
                echo $content;
                echo $more_link_text;
                }
                else {
                // echo '<div class="slide_excerpt">';
                echo $content;
                echo $more_link_text;
                // echo "</div>";
                }
           }
           else {
              if($notagp) {
              echo $content;
              }
              else {
              // echo '<div class="slide_excerpt">';
              echo $content;
              // echo "</div>";
              }
           }
        }

    // Function creates post duplicate as a draft and redirects you to the edit post screen
    function duplicate_post_as_draft(){
        global $wpdb;
        if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'duplicate_post_as_draft' == $_REQUEST['action'] ) ) ) {
            wp_die('No post to duplicate has been supplied!');
        }

        // get the original post id
        $post_id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);

        // and all the original post data then
        $post = get_post( $post_id );

        /* 
         * if you don't want current user to be the new post author,
         * then change next couple of lines to this: $new_post_author = $post->post_author;
         */

        $current_user = wp_get_current_user();
        $new_post_author = $current_user->ID;

        // if post data exists, create the post duplicate
        if (isset( $post ) && $post != null) {

            // new post data array

            $args = array(
                'comment_status' => $post->comment_status,
                'ping_status'    => $post->ping_status,
                'post_author'    => $new_post_author,
                'post_content'   => $post->post_content,
                'post_excerpt'   => $post->post_excerpt,
                'post_name'      => $post->post_name,
                'post_parent'    => $post->post_parent,
                'post_password'  => $post->post_password,
                'post_status'    => 'draft', // You can change it to 'published' if your want duplicate it as a published post
                'post_title'     => $post->post_title,
                'post_type'      => $post->post_type,
                'to_ping'        => $post->to_ping,
                'menu_order'     => $post->menu_order
            );

            // insert the post by wp_insert_post() function

            $new_post_id = wp_insert_post( $args );


            // get all current post terms ad set them to the new post draft

            $taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
            foreach ($taxonomies as $taxonomy) {
                $post_terms = wp_get_object_terms($post_id, $taxonomy);
                for ($i=0; $i<count($post_terms); $i++) {
                    wp_set_object_terms($new_post_id, $post_terms[$i]->slug, $taxonomy, true);
                }
            }

            // duplicate all post meta

            $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
            if (count($post_meta_infos)!=0) {
                $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
                foreach ($post_meta_infos as $meta_info) {
                    $meta_key = $meta_info->meta_key;
                    $meta_value = addslashes($meta_info->meta_value);
                    $sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
                }
                $sql_query.= implode(" UNION ALL ", $sql_query_sel);
                $wpdb->query($sql_query);
            }


            /*
             * finally, redirect to the edit post screen for the new draft
             */
            wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
            exit;
        } else {
            wp_die('Post creation failed, could not find original post: ' . $post_id);
        }
    }

    // Add the duplicate link to action list for post_row_actions

    function duplicate_post_link( $actions, $post ) {
        if (current_user_can('edit_posts')) {
            $actions['duplicate'] = '<a href="admin.php?action=duplicate_post_as_draft&amp;post=' . $post->ID . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
        }
        return $actions;
    }
    
    

   

    /*
    // end qt_custom_breadcrumbs()
    public function custom_key ($key)
    {
    global $post;
    echo get_post_meta($post->ID,$key,true);
    }
    // register sidebars
    register_sidebar(array(
    'name' => __( 'Barre de droite (Accueil)' ),
    'id' => 'main-sidebar',
    'description' => __( 'Les éléments ici aparaîtront dans la barre de droite de la page Accueil.' ),
    'before_widget' => '<div class="wrap-col widget" style="background:#f1f1f1;position:relative;">',
    'after_widget'  => '</div>',
    'before_title'  => '<h2 class="widget-title orange">',
    'after_title'   => '</h2>'));

    // register sidebars
    register_sidebar(array(
    'name' => __( 'Barre de droite (Page)' ),
    'id' => 'page-sidebar',
    'description' => __( 'Les éléments ici aparaîtront dans la barre de droite des pages intérieures' ),
    'before_widget' => '<div class="wrap-col widget" style="background:#f1f1f1;position:relative;">',
    'after_widget'  => '</div>',
    'before_title'  => '<h2 class="widget-title orange">',
    'after_title'   => '</h2>'));

    register_sidebar(array(
    'name' => __( 'Pub du Milieu' ),
    'id' => 'middle-bar',
    'description' => __( "Les éléments ici aparaîtront dans le centre de la page d'Accueil." ),
    'before_widget' => '<div class="col-xs-12 col-sm-3">',
    'after_widget'  => '</div>',
    'before_title'  => '<h6>',
    'after_title'   => '</h6>'));
    register_sidebar(array(
    'name' => __( 'Pub du Haut' ),
    'id' => 'top-bar',
    'description' => __( "Les éléments ici aparaîtront dans le haut de la page d'Accueil." ),
    'before_widget' => '<div class="col-xs-12 col-sm-3">',
    'after_widget'  => '</div>',
    'before_title'  => '<h6>',
    'after_title'   => '</h6>'));

    register_sidebar(array(
    'name' => __( 'Pub du Fond' ),
    'id' => 'background',
    'description' => __( "L'image ici aparaitra en couverture du site" ),
    'before_widget' => '',
    'after_widget'  => '',
    'before_title'  => '',
    'after_title'   => ''));

    */

    // Add wp_nav_menu_objects for parent and child page class
    public function add_menu_parent_class( $items ) {

        $parents = array();
        foreach ( $items as $item ) {
            if ( $item->menu_item_parent && $item->menu_item_parent > 0 ) {
                $parents[] = $item->menu_item_parent;
            }
        }

        foreach ( $items as $item ) {
            if ( in_array( $item->ID, $parents ) ) {
                $item->classes[] = 'menu-parent-item'; 
            }
        }

        return $items;    
    }

    // strip video id from youtube video url
    public function get_video_ID($url){
        parse_str( parse_url( $url, PHP_URL_QUERY ), $my_array_of_vars );
        return $my_array_of_vars['v'];    
          // Output: C4kxS1ksqtw
    }
}