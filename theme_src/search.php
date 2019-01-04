<?php
/**
 * The template for displaying search results pages.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package RPL
 */

get_header(); ?>

    <section id="primary" class="content-area container">
        <main id="main" class="site-main" role="main">
            <?php
            if ( have_posts() ) : ?>
                    <header class="page-header" id="search_page-header">
                            <h1 class="page-title"><?php printf( esc_html__( 'Search Results for: %s', 'rpl' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
                            <hr>
                    </header><!-- .page-header -->
                    <?php
                    /* Start the Loop */
                    while ( have_posts() ) : the_post();

                            /**
                             * Run the loop for the search to output the results.
                             * If you want to overload this in a child theme then include a file
                             * called content-search.php and that will be used instead.
                             */
                            get_template_part( 'template-parts/content', 'search' );
                    endwhile;?>
                    
                    <div class="row bottom_nav_row" id="search_nav_row">
                        <div class="col-sm-6" id="search_previous">
                            <?php previous_posts_link();?>
                        </div>
                        <div class="col-sm-6" id="search_next">
                            <?php next_posts_link();?>
                        </div>
                    </div>
                    
                    
               <?php     
            else :
                    get_template_part( 'template-parts/content', 'none' );
            endif; ?>
        </main><!-- #main -->
    </section><!-- #primary -->

<?php
get_footer();
