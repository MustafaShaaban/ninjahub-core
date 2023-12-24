<?php
    /**
     * @Filename: class-Ninjahub_Post.php
     * @Description: This file contains the Ninjahub_Post class, which represents a NINJAHUB post. It provides methods for retrieving, converting, inserting, updating, and deleting posts.
     * @User: NINJA MASTER - Mustafa Shaaban
     * @Date: 1/4/2023
     */

    namespace NINJAHUB\APP\CLASSES;

    use NINJAHUB\Ninjahub;
    use WP_Error;
    use WP_Post;

    /**
     * The Ninjahub_Post class represents a NINJAHUB post.
     *
     * @class Ninjahub_Post
     * @package NinjaHub
     */
    class Ninjahub_Post
    {

        /**
         * @var \NINJAHUB\APP\CLASSES\Ninjahub_Post|null The singleton instance of the Ninjahub_Post class.
         */
        private static ?Ninjahub_Post $instance = NULL;

        /**
         * @var array The metadata of the post.
         */
        public array $meta_data = [];

        /**
         * @var int The ID of the post.
         */
        protected int $ID = 0;

        /**
         * @var int The author ID of the post.
         */
        protected int $author = 0;

        /**
         * @var string The title of the post.
         */
        protected string $title = '';

        /**
         * @var string The content of the post.
         */
        protected string $content = '';

        /**
         * @var string The excerpt of the post.
         */
        protected string $excerpt = '';

        /**
         * @var string The status of the post.
         */
        protected string $status = 'publish';

        /**
         * @var string The name/slug of the post.
         */
        protected string $name = '';

        /**
         * @var int The parent ID of the post.
         */
        protected int $parent = 0;

        /**
         * @var string The type of the post.
         */
        protected string $type = 'post';

        /**
         * @var string The created date of the post.
         */
        protected string $created_date = '0000-00-00 00:00:00';

        /**
         * @var string The modified date of the post.
         */
        protected string $modified_date = '0000-00-00 00:00:00';

        /**
         * @var string The thumbnail URL of the post.
         */
        protected string $thumbnail = '';

        /**
         * @var string The permalink of the post.
         */
        protected string $link = '';

        /**
         * @var array The taxonomies of the post.
         */
        protected array $taxonomy = [];

        /**
         * Constructs a new Ninjahub_Post object.
         */
        public function __construct()
        {
            // Reformat class metadata
            $this->meta_data = $this->reformat_metadata($this->meta_data);
        }

        /**
         * Magic method to get a property value.
         *
         * @param string $name The name of the property.
         *
         * @return mixed The value of the property or FALSE if the property doesn't exist.
         */
        public function __get($name)
        {
            return property_exists($this, $name) ? $this->{$name} : FALSE;
        }

        /**
         * Magic method to set a property value.
         *
         * @param string $name The name of the property.
         * @param mixed  $value The value to set.
         *
         * @return void
         */
        public function __set($name, $value)
        {
            $this->{$name} = $value;
        }

        /**
         * Returns the Ninjahub_Post instance for the given WP_Post object and metadata.
         *
         * @param WP_Post $post The WP_Post object.
         * @param array   $meta_data The metadata of the post.
         *
         * @return \NINJAHUB\APP\CLASSES\Ninjahub_Post The Ninjahub_Post instance.
         */
        public static function get_post(WP_Post $post, array $meta_data = []): Ninjahub_Post
        {
            $class          = __CLASS__;
            self::$instance = new $class();

            // Reformat class metadata
            $meta_data = self::$instance->reformat_metadata($meta_data);

            return self::$instance->convert($post, $meta_data);
        }

        /**
         * Converts a WP_Post object to a Ninjahub_Post object.
         *
         * @param WP_Post $post The WP_Post object.
         * @param array   $meta_data The metadata of the post.
         *
         * @return \NINJAHUB\APP\CLASSES\Ninjahub_Post The converted Ninjahub_Post object.
         */
        public function convert(WP_Post $post, array $meta_data = []): Ninjahub_Post
        {
            global $wpdb;

            $class    = __CLASS__;
            $new_post = new $class();

            $new_post->ID            = $post->ID;
            $new_post->author        = $post->post_author;
            $new_post->type          = $post->post_type;
            $new_post->name          = $post->post_name;
            $new_post->title         = $post->post_title;
            $new_post->content       = $post->post_content;
            $new_post->excerpt       = $post->post_excerpt;
            $new_post->status        = $post->post_status;
            $new_post->parent        = $post->post_parent;
            $new_post->created_date  = $post->post_date;
            $new_post->modified_date = $post->post_modified;
            $new_post->thumbnail     = get_the_post_thumbnail_url($post);
            $new_post->link          = get_permalink($post->ID);
            $new_post->taxonomy      = [];

            $groupedByTaxonomy = $wpdb->get_results("SELECT tr.term_taxonomy_id AS term_id , t.name, t.slug, tt.parent, tt.taxonomy
																FROM `" . $wpdb->prefix . "term_relationships` tr
																LEFT JOIN `" . $wpdb->prefix . "terms` t ON t.term_id = tr.term_taxonomy_id
																LEFT JOIN `" . $wpdb->prefix . "term_taxonomy` tt ON tt.term_id = t.term_id
																WHERE tr.object_id = '$post->ID' AND tt.taxonomy != 'translation_priority';");

            foreach ($groupedByTaxonomy as $item) {
                $new_post->taxonomy[$item->taxonomy][] = $item;
            }

            if (empty($meta_data)) {
                $meta_data = $wpdb->get_results("SELECT `meta_key`, `meta_value`
                                                                    FROM `" . $wpdb->prefix . "postmeta` AS meta
                                                                    WHERE meta.`post_id` = '$post->ID';");

                foreach ($meta_data as $meta) {
                    $new_post->meta_data[$meta->meta_key] = $meta->meta_value;
                }

            } else {
                foreach ($meta_data as $key => $meta) {
                    $new_post->meta_data[$key] = get_post_meta($post->ID, $key, TRUE);
                }
            }

            return $new_post;
        }

        /**
         * Inserts the post into the database.
         *
         * @return int|WP_Error|Ninjahub_Post The inserted post ID, WP_Error object on failure, or the Ninjahub_Post instance.
         */
        public function insert(): int|WP_Error|Ninjahub_Post
        {
            $insert = wp_insert_post([
                'ID'           => $this->ID,
                'post_title'   => $this->title,
                'post_content' => $this->content,
                'post_excerpt' => $this->excerpt,
                'post_status'  => $this->status,
                'post_parent'  => $this->parent,
                'post_author'  => $this->author,
                'post_name'    => $this->name,
                'post_type'    => $this->type
            ]);

            if (is_wp_error($insert)) {
                return $insert;
            }

            if ($insert) {
                foreach ($this->meta_data as $key => $meta) {
                    add_post_meta($insert, $key, $meta);
                }
                foreach ($this->taxonomy as $tax_name => $taxonomies) {
                    wp_set_post_terms($this->ID, $taxonomies, $tax_name, FALSE);
                }
                $this->ID = $insert;

                do_action(Ninjahub::_DOMAIN_NAME . "_after_insert_" . $this->type, $this);
            }

            return $this;
        }

        /**
         * Updates the post in the database.
         *
         * @return Ninjahub_Post|WP_Error The updated Ninjahub_Post instance or WP_Error object on failure.
         */
        public function update(): Ninjahub_Post|WP_Error
        {
            $update = wp_update_post([
                'ID'           => $this->ID,
                'post_title'   => $this->title,
                'post_content' => $this->content,
                'post_excerpt' => $this->excerpt,
                'post_status'  => $this->status,
                'post_parent'  => $this->parent,
                'post_author'  => $this->author,
                'post_name'    => $this->name
            ]);

            if (is_wp_error($update)) {
                return $update;
            }

            if ($update) {
                foreach ($this->meta_data as $key => $meta) {
                    update_post_meta($update, $key, $meta);
                }

                foreach ($this->taxonomy as $tax_name => $terms) {
                    if (is_object($terms[0])) {
                        $terms = array_map(function($term) {
                            return $term->term_id;
                        }, $terms);
                    }
                    wp_set_post_terms($this->ID, $terms, $tax_name, FALSE);
                }


                do_action(Ninjahub::_DOMAIN_NAME . "_after_update_" . $this->type, $this);
            }

            return $this;
        }

        /**
         * Deletes the post from the database.
         *
         * @param bool $force_delete Whether to bypass trash and force deletion.
         *
         * @return WP_Post The deleted WP_Post object.
         */
        public function delete(bool $force_delete = FALSE): WP_Post
        {
            $delete = wp_delete_post($this->ID, $force_delete);
            do_action(Ninjahub::_DOMAIN_NAME . "_after_delete_" . $this->type, $this->ID);
            return $delete;

        }

        /**
         * Reformat the metadata array by renaming keys.
         *
         * @param array $meta_data The metadata array to reformat.
         *
         * @return array The reformatted metadata array.
         */
        private function reformat_metadata($meta_data): array
        {
            foreach ($meta_data as $k => $meta) {
                $meta_data[$meta] = '';
                unset($meta_data[$k]);
            }

            return $meta_data;
        }

        /**
         * Sets the value of a metadata key.
         *
         * @param string       $name The name of the metadata key.
         * @param string|array $value The value to set for the metadata key.
         *
         * @return bool True if the metadata key exists and the value is set, False otherwise.
         */
        public function set_meta_data(string $name, string|array $value): bool
        {
            if (array_key_exists($name, $this->meta_data)) {
                $this->meta_data[$name] = $value;

                return TRUE;
            }

            return FALSE;
        }

        /**
         * Retrieves the value of a metadata key.
         *
         * @param string $meta_name The name of the metadata key.
         *
         * @return string|bool The value of the metadata key or False if the metadata key doesn't exist.
         */
        public function get($meta_name): string|bool
        {
            return get_post_meta($this->ID, $meta_name, TRUE);
        }

    }