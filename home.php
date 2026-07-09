<?php
/**
 * Blog Archive Template
 * 
 * Displays the blog posts archive (when blog is set as homepage).
 *
 * @package JCP_Core
 */

get_header();
?>

<span id="jcp-app" data-jcp-page="blog" hidden aria-hidden="true"></span>
<main class="jcp-marketing">
  <!-- Hero Section (same spacing as category/author/archive) -->
  <section class="jcp-section rankings-section jcp-archive-hero-section">
    <div class="jcp-container">
      <div class="rankings-header">
        <h1><?php echo esc_html( get_bloginfo( 'name' ) ); ?> Blog</h1>
        <p class="rankings-subtitle"><?php esc_html_e( 'Stories, tips, and proof from the field—so you can win more work.', 'jcp-core' ); ?></p>
      </div>
      <?php
      $posts_page_id = (int) get_option( 'page_for_posts' );
      if ( $posts_page_id ) {
        $page_content = get_post_field( 'post_content', $posts_page_id );
        if ( ! empty( trim( $page_content ) ) ) {
          echo '<div class="jcp-archive-intro">';
          echo apply_filters( 'the_content', $page_content );
          echo '</div>';
        }
      }
      ?>
    </div>
  </section>

  <!-- Blog Posts Section -->
  <section class="jcp-section rankings-section jcp-blog-archive-section">
    <div class="jcp-container">
      <?php
      $blog_has_posts = have_posts();
      if ( $blog_has_posts ) :
        $blog_categories = get_categories( [ 'hide_empty' => true ] );
        $total_posts     = $GLOBALS['wp_query']->found_posts;
        ?>
        <div class="blog-search-wrapper directory-search-wrapper">
          <div class="directory-search blog-search-bar">
            <div class="search-box blog-search-box">
              <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
              </svg>
              <input type="search" class="search-input blog-search-input" placeholder="<?php echo esc_attr( (int) $total_posts === 1 ? __( 'Search 1 post', 'jcp-core' ) : sprintf( __( 'Search %d posts', 'jcp-core' ), (int) $total_posts ) ); ?>" data-placeholder-singular="<?php esc_attr_e( 'Search 1 post', 'jcp-core' ); ?>" data-placeholder-plural="<?php echo esc_attr( __( 'Search %d posts', 'jcp-core' ) ); ?>" autocomplete="off" aria-label="<?php esc_attr_e( 'Search posts', 'jcp-core' ); ?>">
              <button type="button" class="clear-search-btn is-hidden" aria-label="<?php esc_attr_e( 'Clear search', 'jcp-core' ); ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </div>
            <select class="filter-select blog-category-filter" aria-label="<?php esc_attr_e( 'Filter by category', 'jcp-core' ); ?>">
              <option value=""><?php esc_html_e( 'All categories', 'jcp-core' ); ?></option>
              <?php foreach ( $blog_categories as $cat ) : ?>
                <option value="<?php echo esc_attr( $cat->slug ); ?>"><?php echo esc_html( $cat->name ); ?></option>
              <?php endforeach; ?>
            </select>
            <select class="filter-select blog-sort-filter" aria-label="<?php esc_attr_e( 'Sort by date', 'jcp-core' ); ?>">
              <option value="newest"><?php esc_html_e( 'Newest to oldest', 'jcp-core' ); ?></option>
              <option value="oldest"><?php esc_html_e( 'Oldest to newest', 'jcp-core' ); ?></option>
            </select>
            <div class="view-toggle blog-view-toggle" role="group" aria-label="<?php esc_attr_e( 'View layout', 'jcp-core' ); ?>">
              <button type="button" class="view-btn blog-view-grid active" data-view="grid" aria-pressed="true" aria-label="<?php esc_attr_e( 'Grid view', 'jcp-core' ); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
              </button>
              <button type="button" class="view-btn blog-view-list" data-view="list" aria-pressed="false" aria-label="<?php esc_attr_e( 'List view', 'jcp-core' ); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
              </button>
            </div>
            <button type="button" class="clear-filters-btn is-hidden blog-clear-filters"><?php esc_html_e( 'Clear filters', 'jcp-core' ); ?></button>
          </div>
        </div>

        <div class="jcp-blog-grid" id="blog-posts-container">
          <?php
          while ( have_posts() ) :
            the_post();
            get_template_part( 'templates/content/content', 'post-card' );
          endwhile;
          ?>
        </div>

        <?php
        get_template_part( 'templates/partials/pagination' );
      else :
        get_template_part( 'templates/content/content', 'none' );
      endif;
      ?>
    </div>
  </section>

  <?php
  if ( ! empty( $blog_has_posts ) && function_exists( 'jcp_blog_conversion_render_archive_strip' ) ) {
    jcp_blog_conversion_render_archive_strip();
  }
  ?>
</main>

<?php
if ( ! empty( $blog_has_posts ) && function_exists( 'jcp_blog_conversion_render_sticky' ) ) {
  jcp_blog_conversion_render_sticky();
}
?>

<?php if ( ! empty( $blog_has_posts ) ) : ?>
<script>
(function() {
  var container = document.getElementById('blog-posts-container');
  var searchInput = document.querySelector('.blog-search-input');
  var categoryFilter = document.querySelector('.blog-category-filter');
  var sortFilter = document.querySelector('.blog-sort-filter');
  var clearBtn = document.querySelector('.blog-clear-filters');
  var clearSearchBtn = document.querySelector('.blog-search-box .clear-search-btn');
  var viewGridBtn = document.querySelector('.blog-view-grid');
  var viewListBtn = document.querySelector('.blog-view-list');
  if (!container || !searchInput || !categoryFilter) return;
  var cards = Array.prototype.slice.call(container.querySelectorAll('.jcp-post-card'));

  var STORAGE_KEY = 'jcp_blog_view';
  var savedView = typeof localStorage !== 'undefined' ? localStorage.getItem(STORAGE_KEY) : null;
  if (savedView === 'list') {
    container.classList.add('jcp-blog-list');
    if (viewGridBtn) { viewGridBtn.classList.remove('active'); viewGridBtn.setAttribute('aria-pressed', 'false'); }
    if (viewListBtn) { viewListBtn.classList.add('active'); viewListBtn.setAttribute('aria-pressed', 'true'); }
  }

  function setView(view) {
    if (view === 'list') {
      container.classList.add('jcp-blog-list');
      if (viewGridBtn) { viewGridBtn.classList.remove('active'); viewGridBtn.setAttribute('aria-pressed', 'false'); }
      if (viewListBtn) { viewListBtn.classList.add('active'); viewListBtn.setAttribute('aria-pressed', 'true'); }
    } else {
      container.classList.remove('jcp-blog-list');
      if (viewGridBtn) { viewGridBtn.classList.add('active'); viewGridBtn.setAttribute('aria-pressed', 'true'); }
      if (viewListBtn) { viewListBtn.classList.remove('active'); viewListBtn.setAttribute('aria-pressed', 'false'); }
    }
    if (typeof localStorage !== 'undefined') localStorage.setItem(STORAGE_KEY, view);
  }
  if (viewGridBtn) viewGridBtn.addEventListener('click', function() { setView('grid'); });
  if (viewListBtn) viewListBtn.addEventListener('click', function() { setView('list'); });

  function applySort() {
    var order = (sortFilter && sortFilter.value) ? sortFilter.value : 'newest';
    var sorted = cards.slice().sort(function(a, b) {
      var da = a.getAttribute('data-date') || '';
      var db = b.getAttribute('data-date') || '';
      if (order === 'oldest') return da.localeCompare(db);
      return db.localeCompare(da);
    });
    sorted.forEach(function(card) { container.appendChild(card); });
  }
  if (sortFilter) sortFilter.addEventListener('change', applySort);

  function updatePlaceholder(visible) {
    var singular = searchInput.getAttribute('data-placeholder-singular') || 'Search 1 post';
    var pluralTpl = searchInput.getAttribute('data-placeholder-plural') || 'Search %d posts';
    searchInput.placeholder = visible === 1 ? singular : pluralTpl.replace('%d', visible);
  }

  function filterPosts() {
    var q = (searchInput.value || '').trim().toLowerCase();
    var cat = (categoryFilter.value || '').trim();
    var visible = 0;
    cards.forEach(function(card) {
      var title = (card.getAttribute('data-title') || '').toLowerCase();
      var excerpt = (card.getAttribute('data-excerpt') || '').toLowerCase();
      var categories = (card.getAttribute('data-categories') || '').split(/\s+/).filter(Boolean);
      var matchSearch = !q || title.indexOf(q) !== -1 || excerpt.indexOf(q) !== -1;
      var matchCat = !cat || categories.indexOf(cat) !== -1;
      var show = matchSearch && matchCat;
      card.style.display = show ? '' : 'none';
      if (show) visible++;
    });
    updatePlaceholder(visible);
    if (clearBtn) clearBtn.classList.toggle('is-hidden', !q && !cat);
  }
  function toggleClearSearch() {
    if (clearSearchBtn) clearSearchBtn.classList.toggle('is-hidden', !searchInput.value.trim());
  }
  searchInput.addEventListener('input', function() { filterPosts(); toggleClearSearch(); });
  searchInput.addEventListener('keyup', function() { filterPosts(); toggleClearSearch(); });
  categoryFilter.addEventListener('change', filterPosts);
  if (clearBtn) clearBtn.addEventListener('click', function() {
    searchInput.value = '';
    categoryFilter.value = '';
    filterPosts();
    toggleClearSearch();
    clearBtn.classList.add('is-hidden');
  });
  if (clearSearchBtn) clearSearchBtn.addEventListener('click', function() {
    searchInput.value = '';
    searchInput.focus();
    filterPosts();
    clearSearchBtn.classList.add('is-hidden');
  });
  toggleClearSearch();
  filterPosts();
})();
</script>
<?php endif; ?>

<?php
get_footer();
