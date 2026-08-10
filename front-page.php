<?php
/**
 * Página inicial da COMPIA Editora.
 *
 * O WordPress prioriza este arquivo na raiz do site mesmo com
 * "show_on_front = posts": is_front_page() é verdadeiro na home e a hierarquia
 * de templates checa front-page.php antes de home/index.
 *
 * Estrutura espelhada de page.php do Storefront (#primary > #main + sidebar)
 * para herdar cabeçalho, rodapé e o container .col-full do tema-pai.
 *
 * @package compia-child
 */

get_header();

$link_loja = wc_get_page_permalink('shop');

// Categorias em destaque na ordem desejada; os dados (nome, descrição, link)
// vêm dos termos reais do WooCommerce, nada fixo no template.
$slugs_categorias = ['livros-fisicos', 'e-books', 'kits'];
?>

<div id="primary" class="content-area">
	<main id="main" class="site-main compia-home" role="main">

		<section class="compia-home__hero">
			<div class="compia-home__hero-conteudo">
				<h1 class="compia-home__titulo"><?php echo esc_html(get_bloginfo('name')); ?></h1>
				<p class="compia-home__tagline"><?php echo esc_html(get_bloginfo('description')); ?></p>
				<p class="compia-home__apresentacao">
					A COMPIA Editora publica livros técnicos sobre inteligência artificial,
					blockchain, criptografia e cibersegurança — do primeiro contato à prática
					profissional, com linguagem clara e exemplos aplicados.
				</p>
				<?php if ($link_loja) : ?>
					<a class="compia-home__cta" href="<?php echo esc_url($link_loja); ?>">
						Conhecer o catálogo
					</a>
				<?php endif; ?>
			</div>
		</section>

		<section class="compia-home__secao" aria-labelledby="compia-home-categorias">
			<h2 id="compia-home-categorias" class="compia-home__secao-titulo">Categorias em destaque</h2>
			<div class="compia-home__categorias">
				<?php foreach ($slugs_categorias as $slug) :
					$categoria = get_term_by('slug', $slug, 'product_cat');

					// Categoria inexistente é ignorada em vez de gerar link quebrado.
					if (!$categoria) {
						continue;
					}

					$url = compia_url_termo($categoria);

					if ('' === $url) {
						continue;
					}
					?>
					<a class="compia-home__categoria" href="<?php echo esc_url($url); ?>">
						<h3 class="compia-home__categoria-titulo"><?php echo esc_html($categoria->name); ?></h3>
						<?php if ($categoria->description) : ?>
							<p class="compia-home__categoria-texto"><?php echo esc_html($categoria->description); ?></p>
						<?php endif; ?>
					</a>
				<?php endforeach; ?>
			</div>
		</section>

		<?php
		// Produtos reais mais recentes. Reaproveita o loop nativo do WooCommerce
		// (woocommerce_product_loop_start + content-product) para gerar o mesmo
		// markup da loja e herdar o CSS de .compia-catalogo sem duplicá-lo.
		$destaques = new WP_Query([
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 3,
			'orderby'        => 'date',
			'order'          => 'DESC',
		]);

		if ($destaques->have_posts()) : ?>
			<section class="compia-home__secao" aria-labelledby="compia-home-produtos">
				<h2 id="compia-home-produtos" class="compia-home__secao-titulo">Lançamentos</h2>
				<?php
				woocommerce_product_loop_start();

				while ($destaques->have_posts()) :
					$destaques->the_post();
					wc_get_template_part('content', 'product');
				endwhile;

				woocommerce_product_loop_end();
				?>
			</section>
		<?php endif;

		wp_reset_postdata();
		?>

	</main><!-- #main -->
</div><!-- #primary -->

<?php
do_action('storefront_sidebar');
get_footer();
