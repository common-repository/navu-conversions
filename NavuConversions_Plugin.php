<?php

include_once 'NavuConversions_LifeCycle.php';
include_once 'NavuConversions_Widgets.php';

class NavuConversions_Plugin extends NavuConversions_LifeCycle
{
/**
 * See: http://plugin.michael-simpson.com/?page_id=31
 * @return array of option meta data.
 */
    public function getOptionMetaData()
    {
//  http://plugin.michael-simpson.com/?page_id=31
        return array(
            'NavuSiteCode' => array(__('Site Code', 'navu-conversions')),
            'NavuAppSiteCode' => array(__('Legacy Code', 'navu-conversions')),
            'Support1' => array(__('Support (1)', 'navu-conversions')),
            'Support2' => array(__('Support (2)', 'navu-conversions')),
        );
    }

//    protected function getOptionValueI18nString($optionValue) {
    //        $i18nValue = parent::getOptionValueI18nString($optionValue);
    //        return $i18nValue;
    //    }

    protected function initOptions()
    {
        $options = $this->getOptionMetaData();
        if (!empty($options)) {
            foreach ($options as $key => $arr) {
                if (is_array($arr) && count($arr) > 1) {
                    $this->addOption($key, $arr[1]);
                }
            }
        }
    }

    public function getPluginDisplayName()
    {
        return 'Navu Conversions';
    }

    protected function getMainPluginFileName()
    {
        return 'navu-conversions.php';
    }

/**
 * See: http://plugin.michael-simpson.com/?page_id=101
 * Called by install() to create any database tables if needed.
 * Best Practice:
 * (1) Prefix all table names with $wpdb->prefix
 * (2) make table names lower case only
 * @return void
 */
    protected function installDatabaseTables()
    {
//        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("CREATE TABLE IF NOT EXISTS `$tableName` (
        //            `id` INTEGER NOT NULL");
    }

/**
 * See: http://plugin.michael-simpson.com/?page_id=101
 * Drop plugin-created tables on uninstall.
 * @return void
 */
    protected function unInstallDatabaseTables()
    {
//        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("DROP TABLE IF EXISTS `$tableName`");
    }

/**
 * Perform actions when upgrading from version X to version Y
 * See: http://plugin.michael-simpson.com/?page_id=35
 * @return void
 */
    public function upgrade()
    {
    }

    public function addActionsAndFilters()
    {
// Add options administration page
        // http://plugin.michael-simpson.com/?page_id=47
        add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));

// Add Actions & Filters
        // http://plugin.michael-simpson.com/?page_id=37

        add_action('wp_head', array(&$this, 'addNavuPageHeader'));

// Register AJAX hooks
        // http://plugin.michael-simpson.com/?page_id=41

        // Ensure pages can be configured with categories and tags
        add_action('init', array(&$this, 'add_taxonomies_to_pages'));

        $prefix = is_network_admin() ? 'network_admin_' : '';
        $plugin_file = plugin_basename($this->getPluginDir() . DIRECTORY_SEPARATOR . $this->getMainPluginFileName()); //plugin_basename( $this->getMainPluginFileName() );
        // $this->guildLog('Adding filter ' . "{$prefix}plugin_action_links_{$plugin_file}");
        add_filter("{$prefix}plugin_action_links_{$plugin_file}", array(&$this, 'onActionLinks'));
        add_filter('rocket_delay_js_exclusions', array(&$this, 'np_wp_rocket__exclude_from_delay_js'));
    }

    // Exclude scripts from JS delay.
    public function np_wp_rocket__exclude_from_delay_js($excluded_strings = array())
    {
        // MUST ESCAPE PERIODS AND PARENTHESES!
        $excluded_strings[] = "navu";
        return $excluded_strings;
    }

    public function onActionLinks($links)
    {
        // $this->guildLog('onActionLinks ' . admin_url('options-general.php?page=NavuConversions_PluginSettings'));
        $mylinks = array('<a href="' . admin_url('options-general.php?page=NavuConversions_PluginSettings') . '">Settings</a>');
        return array_merge($links, $mylinks);
    }


    public function add_taxonomies_to_pages()
    {
        register_taxonomy_for_object_type('post_tag', 'page');
        register_taxonomy_for_object_type('category', 'page');
    }

/* determine whether post has a featured image, if not, find the first image inside the post content, $size passes the thumbnail size, $url determines whether to return a URL or a full image tag*/
/* adapted from http://www.amberweinberg.com/wordpress-find-featured-image-or-first-image-in-post-find-dimensions-id-by-url/ */

    public function getPostImage($post)
    {
        ob_start();
        ob_end_clean();

/*If there's a featured image, show it*/

        if (has_post_thumbnail($post)) {
            $images = wp_get_attachment_image_src(get_post_thumbnail_id($post), 'single-post-thumbnail');
            return $images[0];
        } else {
            $content = $post->post_content;
            $first_img = '';
            $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches);
            $first_img = $matches[1][0];

            /*No featured image, so we get the first image inside the post content*/

            if ($first_img) {
                return $first_img;
            } else {
                return null;
            }
        }
    }

    public function removeSemicolons($value)
    {
        return str_replace(';', ' ', $value);
    }

    public function addNavuPageHeader()
    {
        global $post;
        echo "\n";
        echo '<meta name="navu:wpversion" content="1.0.4" />' . "\n";
        $navuSiteCode = trim($this->getOption('NavuSiteCode'));
        if ($navuSiteCode) {
          echo '<meta name="navu:site" content="' . esc_attr($navuSiteCode) . '">' . "\n";
          $serverUrl = trim($this->getOption('Support1', 'https://embed.navu.co'));
          if ($serverUrl != 'https://embed.navu.co') {
            wp_print_inline_script_tag(
              'window.$navu = window.$navu || {}; window.$navu.host = "' . esc_url($serverUrl) . '";', 
              array(
              'id' => 'navu-host',
              'async' => true
            ));
          }
          wp_print_script_tag(array(
            'id' => 'navu-boot',
            'src' => esc_url($serverUrl . '/boot.js'), 
            'async' => true)
          );
        }
        $navuAppSiteCode = trim($this->getOption('NavuAppSiteCode'));
        if ($navuAppSiteCode) {
          $navuAppServerUrl = trim($this->getOption('Support2', 'https://app.navu.app'));
          wp_print_inline_script_tag(
            '"use strict";(async(e,t)=>{if(location.search.indexOf("no-navu")>=0){return}let o;const a=()=>(performance||Date).now();const i=window.$slickBoot={rt:e,_es:a(),ev:"2.0.0",l:async(e,t)=>{try{let i=0;if(!o&&"caches"in self){o=await caches.open("slickstream-code")}if(o){let n=await o.match(e);if(!n){i=a();await o.add(e);n=await o.match(e);if(n&&!n.ok){n=undefined;o.delete(e)}}if(n){return{t:i,d:t?await n.blob():await n.json()}}}}catch(e){console.log(e)}return{}}};const n=e=>new Request(e,{cache:"no-store"});const c=n(`${e}/d/page-boot-data?${innerWidth<=600?"mobile&":""}site=${t}&url=${encodeURIComponent(location.href.split("#")[0])}`);let{t:s,d:l}=await i.l(c);if(l){if(l.bestBy<Date.now()){l=undefined}else if(s){i._bd=s}}if(!l){i._bd=a();l=await(await fetch(c)).json()}if(l){i.d=l;let e=l.bootUrl;const{t:t,d:o}=await i.l(n(e),true);if(o){i.bo=e=URL.createObjectURL(o);if(t){i._bf=t}}else{i._bf=a()}const c=document.createElement("script");c.src=e;document.head.appendChild(c)}else{console.log("[NavuApp] Boot failed")}}) ("' . esc_url($navuAppServerUrl) . '","' . esc_html($navuAppSiteCode) . '");',
            array(
              'id' => 'navu-app-inline',
              'async' => true)
            );
        }

        $ldJsonElements = array();

        $ldJsonPlugin = (object) [
            '@type' => 'Plugin',
            'version' => '1.0.4',
        ];
        array_push($ldJsonElements, $ldJsonPlugin);

        $ldJsonSite = (object) [
            '@type' => 'Site',
            'name' => esc_html(get_bloginfo('name')),
            'url' => esc_url(get_bloginfo('url')),
            'description' => esc_html(get_bloginfo('description')),
            'atomUrl' => esc_url(get_bloginfo('atom_url')),
            'rtl' => is_rtl(),
        ];
        array_push($ldJsonElements, $ldJsonSite);

        if (!empty($post)) {
            $pageType = 'post';
            if (is_front_page() || is_home()) {
                $pageType = 'home';
            } else if (is_category()) {
                $pageType = 'category';
            } else if (is_tag()) {
                $pageType = 'tag';
            } else if (is_singular('post')) {
                $pageType = 'post';
            } else if (is_singular('page')) {
                $pageType = 'page';
            } else {
                $pageType = 'other';
            }
            $ldJsonPost = (object) [
                '@type' => 'WebPage',
                '@id' => esc_html($post->ID),
                'isFront' => is_front_page(),
                'isHome' => is_home(),
                'isCategory' => is_category(),
                'isTag' => is_tag(),
                'isSingular' => is_singular(),
                'date' => get_the_time('c'),
                'modified' => get_the_modified_time('c'),
                'title' => esc_html($post->post_title),
                'pageType' => esc_html($pageType),
                'postType' => esc_html($post->post_type),
            ];
            if (has_post_thumbnail($post)) {
                $images = wp_get_attachment_image_src(get_post_thumbnail_id($post), 'single-post-thumbnail');
                if (!empty($images)) {
                    $ldJsonPost->featured_image = esc_url($images[0]);
                }
            }
            $authorName = get_the_author_meta('display_name');
            if (!empty($authorName)) {
                $ldJsonPost->author = esc_html($authorName);
            }
            if (is_category()) {
                $term = get_queried_object();
                if (isset($term->slug)) {
                    $ldJsonCategory = (object) [
                        '@id' => esc_html($term->term_id),
                        'slug' => esc_html($term->slug),
                        'name' => esc_html($term->name),
                    ];
                    $ldJsonPost->category = $ldJsonCategory;
                }
            } else if (is_tag()) {
                $term = get_queried_object();
                if (isset($term->slug)) {
                    $ldJsonTag = (object) [
                        '@id' => esc_html($term->term_id),
                        'slug' => esc_html($term->slug),
                        'name' => esc_html($term->name),
                    ];
                    $ldJsonPost->tag = $ldJsonTag;
                }
            } else if (is_singular(['post', 'page'])) {
                $categories = get_the_category();
                if (!empty($categories)) {
                    $ldJsonCategoryElements = array();
                    foreach ($categories as $category) {
                        if (isset($category->slug) && $category->slug !== 'uncategorized') {
                            $used = [$category->cat_ID];
                            $count = 0;
                            $parentCatId = $category->category_parent;
                            $ldJsonParents = array();
                            while ($parentCatId && $count < 8 && !in_array($parentCatId, $used)) {
                                $parentCat = get_category($parentCatId);
                                if (isset($parentCat->slug) && $parentCat->slug !== 'uncategorized') {
                                    $parentCatId = $parentCat->cat_ID;
                                    $ldJsonParent = (object) [
                                        '@type' => esc_html('CategoryParent'),
                                        '@id' => esc_html($parentCat->cat_ID),
                                        'slug' => esc_html($parentCat->slug),
                                        'name' => esc_html($this->removeSemicolons($parentCat->name)),
                                    ];
                                    array_push($ldJsonParents, $ldJsonParent);
                                } else {
                                    break;
                                }
                                array_push($used, $parentCatId);
                                $count = $count + 1;
                            }
                            $ldJsonCategoryElement = (object) [
                                '@id' => esc_html($category->cat_ID),
                                'slug' => esc_html($category->slug),
                                'name' => esc_html($this->removeSemicolons($category->name)),
                                'parents' => $ldJsonParents,
                            ];
                            array_push($ldJsonCategoryElements, $ldJsonCategoryElement);
                        }
                    }
                    if (!empty($ldJsonCategoryElements)) {
                        $ldJsonPost->categories = $ldJsonCategoryElements;
                    }
                }

                $tags = get_the_tags();
                if (!empty($tags)) {
                    $ldJsonTags = array();
                    foreach ($tags as $tag) {
                        if (isset($tag->name)) {
                            array_push($ldJsonTags, esc_html($tag->name));
                        }
                    }
                    if (!empty($ldJsonTags)) {
                        $ldJsonPost->tags = $ldJsonTags;
                    }
                }

                $ldJsonTaxonomies = array();
                $taxonomies = get_object_taxonomies($post, 'objects');
                if (!empty($taxonomies)) {
                    foreach ($taxonomies as $taxonomy) {
                        if (empty($taxonomy->_builtin) && $taxonomy->public) {
                            $taxTerms = array();
                            $terms = get_the_terms($post, $taxonomy->name);
                            if (!empty($terms)) {
                                foreach ($terms as $term) {
                                    $termObject = (object) [
                                        '@id' => esc_html($term->term_id),
                                        'name' => esc_html($term->name),
                                        'slug' => esc_html($term->slug),
                                    ];
                                    array_push($taxTerms, $termObject);
                                }
                                $ldJsonTaxElement = (object) [
                                    'name' => esc_html($taxonomy->name),
                                    'label' => esc_html($taxonomy->label),
                                    'description' => esc_html($taxonomy->description),
                                    'terms' => $taxTerms,
                                ];
                                array_push($ldJsonTaxonomies, $ldJsonTaxElement);
                            }
                        }
                    }
                }
                $ldJsonPost->taxonomies = $ldJsonTaxonomies;
            }
            array_push($ldJsonElements, $ldJsonPost);
        }
        $ldJson = (object) [
            '@context' => esc_url('https://slickstream.com'),
            '@graph' => $ldJsonElements,
        ];
        echo '<script type="application/x-slickstream+json">' . json_encode($ldJson, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
}
