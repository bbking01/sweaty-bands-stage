<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_Shoppercategories_Block_Adminhtml_Shoppercategories_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {

	  $model = Mage::registry('shoppercategories_shoppercategories');

      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('shoppercategories_form', array('legend'=>Mage::helper('shoppercategories')->__('Scheme information')));

      $fieldset->addType('queldorei_color','Queldorei_Shoppercategories_Lib_Varien_Data_Form_Element_QueldoreiColor');
      $fieldset->addType('queldorei_font','Queldorei_Shoppercategories_Lib_Varien_Data_Form_Element_QueldoreiFont');


      if (!Mage::app()->isSingleStoreMode()) {
        $fieldset->addField('store_id', 'multiselect', array(
              'name'      => 'stores[]',
              'label'     => Mage::helper('shoppercategories')->__('Store View'),
              'title'     => Mage::helper('shoppercategories')->__('Store View'),
              'required'  => true,
              'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
          ));
      }
      else {
          $fieldset->addField('store_id', 'hidden', array(
              'name'      => 'stores[]',
              'value'     => Mage::app()->getStore(true)->getId()
          ));
      }

      $fieldset->addField('category_id', 'text', array(
          'label'     => Mage::helper('shoppercategories')->__('Category id'),
          'required'  => false,
          'name'      => 'category_id',
      ));

      $fieldset->addField('apply_child', 'select', array(
          'label'     => Mage::helper('shoppercategories')->__('Apply Scheme to Subcategories?'),
          'name'      => 'apply_child',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('shoppercategories')->__('Yes'),
              ),
              array(
                  'value'     => 0,
                  'label'     => Mage::helper('shoppercategories')->__('No'),
              ),
          ),
      ));

      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('shoppercategories')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('shoppercategories')->__('Enabled'),
              ),
              array(
                  'value'     => 2,
                  'label'     => Mage::helper('shoppercategories')->__('Disabled'),
              ),
          ),
      ));

      $fieldset->addField('enable_font', 'select', array(
          'label'     => Mage::helper('shoppercategories')->__('Enable Google Font'),
          'name'      => 'enable_font',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('shoppercategories')->__('Enabled'),
              ),
              array(
                  'value'     => 2,
                  'label'     => Mage::helper('shoppercategories')->__('Disabled'),
              ),
          ),
      ));

      $fieldset->addField('font', 'queldorei_font', array(
          'label'     => Mage::helper('shoppercategories')->__('Font'),
          'name'      => 'font',
          'values'    => $this->_getGoogleFonts(),
      ));
      $fieldset->addField('color', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Theme Color'),
          'required'  => false,
          'name'      => 'color',
      ));
      $fieldset->addField('title_color', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Title Color'),
          'required'  => false,
          'name'      => 'title_color',
      ));
      $fieldset->addField('header_bg', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Header bg Color'),
          'required'  => false,
          'name'      => 'header_bg',
      ));
      $fieldset->addField('menu_text_color', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Navigation Color'),
          'required'  => false,
          'name'      => 'menu_text_color',
      ));
      $fieldset->addField('slideshow_bg', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Slideshow bg Color'),
          'required'  => false,
          'name'      => 'slideshow_bg',
      ));
      $fieldset->addField('content_bg', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Content bg Color'),
          'required'  => false,
          'name'      => 'content_bg',
      ));

      $fieldset->addField('content_bg_img', 'image', array(
          'label'     => Mage::helper('shoppercategories')->__('Content Background Image'),
          'required'  => false,
          'name'      => 'content_bg_img',
      ));
      $fieldset->addField('content_bg_img_mode', 'select', array(
          'label'     => Mage::helper('shoppercategories')->__('Content Background Image Display mode'),
          'name'      => 'content_bg_img_mode',
          'values'    => array(
              array(
                  'value'     => 'stretch',
                  'label'     => Mage::helper('shoppercategories')->__('stretch'),
              ),
              array(
                  'value'     => 'tile',
                  'label'     => Mage::helper('shoppercategories')->__('tile'),
              ),
          ),
      ));

      $fieldset->addField('content_link', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Content link Color'),
          'required'  => false,
          'name'      => 'content_link',
      ));
      $fieldset->addField('content_link_hover', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Content link Mouseover Color'),
          'required'  => false,
          'name'      => 'content_link_hover',
      ));
      $fieldset->addField('page_title_bg', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Page title bg Color'),
          'required'  => false,
          'name'      => 'page_title_bg',
      ));
      $fieldset->addField('toolbar_bg', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Toolbar bg Color'),
          'required'  => false,
          'name'      => 'toolbar_bg',
      ));
      $fieldset->addField('toolbar_color', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Toolbar Color'),
          'required'  => false,
          'name'      => 'toolbar_color',
      ));
      $fieldset->addField('toolbar_hover_color', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Toolbar Mouseover Color'),
          'required'  => false,
          'name'      => 'toolbar_hover_color',
      ));
      $fieldset->addField('footer_bg', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Footer bg Color'),
          'required'  => false,
          'name'      => 'footer_bg',
      ));
      $fieldset->addField('footer_color', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Footer Color'),
          'required'  => false,
          'name'      => 'footer_color',
      ));
      $fieldset->addField('footer_hover_color', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Footer Mouseover Color'),
          'required'  => false,
          'name'      => 'footer_hover_color',
      ));
      $fieldset->addField('footer_banners_bg', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Footer banners bg Color'),
          'required'  => false,
          'name'      => 'footer_banners_bg',
      ));
      $fieldset->addField('footer_info_bg', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Footer info bg Color'),
          'required'  => false,
          'name'      => 'footer_info_bg',
      ));
      $fieldset->addField('footer_info_border', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Footer info border Color'),
          'required'  => false,
          'name'      => 'footer_info_border',
      ));
      $fieldset->addField('footer_info_title_color', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Footer info Title Color'),
          'required'  => false,
          'name'      => 'footer_info_title_color',
      ));
      $fieldset->addField('footer_info_color', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Footer info Color'),
          'required'  => false,
          'name'      => 'footer_info_color',
      ));
      $fieldset->addField('footer_info_link_color', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Footer info link Color'),
          'required'  => false,
          'name'      => 'footer_info_link_color',
      ));
      $fieldset->addField('footer_info_link_hover_color', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Footer info link mouseover Color'),
          'required'  => false,
          'name'      => 'footer_info_link_hover_color',
      ));
      $fieldset->addField('price_font', 'queldorei_font', array(
          'label'     => Mage::helper('shoppercategories')->__('Price Font'),
          'name'      => 'price_font',
          'values'    => $this->_getGoogleFonts(),
      ));
      $fieldset->addField('price_color', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Price Color'),
          'required'  => false,
          'name'      => 'price_color',
      ));
      $fieldset->addField('price_circle_color', 'queldorei_color', array(
          'label'     => Mage::helper('shoppercategories')->__('Price circle Color'),
          'required'  => false,
          'name'      => 'price_circle_color',
      ));

      if ( Mage::getSingleton('adminhtml/session')->getShoppercategoriesData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getShoppercategoriesData());
          Mage::getSingleton('adminhtml/session')->getShoppercategoriesData(null);
      } elseif ( Mage::registry('shoppercategories_data') ) {
          $form->setValues(Mage::registry('shoppercategories_data')->getData());
      }
      return parent::_prepareForm();
  }

  private function _getGoogleFonts() {
      $gfonts447 = "Abel,Abril Fatface,Aclonica,Acme,Actor,Adamina,Aguafina Script,Aladin,Aldrich,Alegreya,Alegreya SC,Alex Brush,Alfa Slab One,Alice,Alike,Alike Angular,Allan,Allerta,Allerta Stencil,Allura,Almendra,Almendra SC,Amaranth,Amatic SC,Amethysta,Andada,Andika,Annie Use Your Telescope,Anonymous Pro,Antic,Anton,Arapey,Arbutus,Architects Daughter,Arimo,Arizonia,Armata,Artifika,Arvo,Asap,Asset,Astloch,Asul,Atomic Age,Aubrey,Bad Script,Balthazar,Bangers,Basic,Baumans,Belgrano,Bentham,Bevan,Bigshot One,Bilbo,Bilbo Swash Caps,Bitter,Black Ops One,Bonbon,Boogaloo,Bowlby One,Bowlby One SC,Brawler,Bree Serif,Bubblegum Sans,Buda,Buenard,Butcherman,Butterfly Kids,Cabin,Cabin Condensed,Cabin Sketch,Caesar Dressing,Cagliostro,Calligraffitti,Cambo,Candal,Cantarell,Cardo,Carme,Carter One,Caudex,Cedarville Cursive,Ceviche One,Changa One,Chango,Chelsea Market,Cherry Cream Soda,Chewy,Chicle,Chivo,Coda,Coda Caption,Comfortaa,Coming Soon,Concert One,Condiment,Contrail One,Convergence,Cookie,Copse,Corben,Cousine,Coustard,Covered By Your Grace,Crafty Girls,Creepster,Crete Round,Crimson Text,Crushed,Cuprum,Damion,Dancing Script,Dawning of a New Day,Days One,Delius,Delius Swash Caps,Delius Unicase,Devonshire,Didact Gothic,Diplomata,Diplomata SC,Dorsa,Dr Sugiyama,Droid Sans,Droid Sans Mono,Droid Serif,Duru Sans,Dynalight,EB Garamond,Eater,Electrolize,Emblema One,Engagement,Enriqueta,Erica One,Esteban,Euphoria Script,Ewert,Exo,Expletus Sans,Fanwood Text,Fascinate,Fascinate Inline,Federant,Federo,Felipa,Fjord One,Flamenco,Flavors,Fondamento,Fontdiner Swanky,Forum,Francois One,Fredericka the Great,Fresca,Frijole,Fugaz One,Galdeano,Gentium Basic,Gentium Book Basic,Geo,Geostar,Geostar Fill,Germania One,Give You Glory,Glegoo,Gloria Hallelujah,Goblin One,Gochi Hand,Goudy Bookletter 1911,Gravitas One,Gruppo,Gudea,Habibi,Hammersmith One,Handlee,Herr Von Muellerhoff,Holtwood One SC,Homemade Apple,Homenaje,IM Fell DW Pica,IM Fell DW Pica SC,IM Fell Double Pica,IM Fell Double Pica SC,IM Fell English,IM Fell English SC,IM Fell French Canon,IM Fell French Canon SC,IM Fell Great Primer,IM Fell Great Primer SC,Iceberg,Iceland,Inconsolata,Inder,Indie Flower,Inika,Irish Grover,Istok Web,Italianno,Jim Nightshade,Jockey One,Josefin Sans,Josefin Slab,Judson,Julee,Junge,Jura,Just Another Hand,Just Me Again Down Here,Kameron,Kaushan Script,Kelly Slab,Kenia,Knewave,Kotta One,Kranky,Kreon,Kristi,La Belle Aurore,Lancelot,Lato,League Script,Leckerli One,Lekton,Lemon,Lilita One,Limelight,Linden Hill,Lobster,Lobster Two,Lora,Love Ya Like A Sister,Loved by the King,Luckiest Guy,Lusitana,Lustria,Macondo,Macondo Swash Caps,Magra,Maiden Orange,Mako,Marck Script,Marko One,Marmelad,Marvel,Mate,Mate SC,Maven Pro,Meddon,MedievalSharp,Medula One,Megrim,Merienda One,Merriweather,Metamorphous,Metrophobic,Michroma,Miltonian,Miltonian Tattoo,Miniver,Miss Fajardose,Modern Antiqua,Molengo,Monofett,Monoton,Monsieur La Doulaise,Montaga,Montez,Montserrat,Mountains of Christmas,Mr Bedfort,Mr Dafoe,Mr De Haviland,Mrs Saint Delafield,Mrs Sheppards,Muli,Neucha,Neuton,News Cycle,Niconne,Nixie One,Nobile,Norican,Nosifer,Nothing You Could Do,Noticia Text,Nova Cut,Nova Flat,Nova Mono,Nova Oval,Nova Round,Nova Script,Nova Slim,Nova Square,Numans,Nunito,Old Standard TT,Oldenburg,Open Sans,Open Sans Condensed,Orbitron,Original Surfer,Oswald,Over the Rainbow,Overlock,Overlock SC,Ovo,PT Sans,PT Sans Caption,PT Sans Narrow,PT Serif,PT Serif Caption,Pacifico,Parisienne,Passero One,Passion One,Patrick Hand,Patua One,Paytone One,Permanent Marker,Petrona,Philosopher,Piedra,Pinyon Script,Plaster,Play,Playball,Playfair Display,Podkova,Poller One,Poly,Pompiere,Port Lligat Sans,Port Lligat Slab,Prata,Princess Sofia,Prociono,Puritan,Quantico,Quattrocento,Quattrocento Sans,Questrial,Quicksand,Qwigley,Radley,Raleway,Rammetto One,Rancho,Rationale,Redressed,Reenie Beanie,Ribeye,Ribeye Marrow,Righteous,Rochester,Rock Salt,Rokkitt,Ropa Sans,Rosario,Rouge Script,Ruda,Ruge Boogie,Ruluko,Ruslan Display,Ruthie,Sail,Salsa,Sancreek,Sansita One,Sarina,Satisfy,Schoolbell,Shadows Into Light,Shanti,Share,Shojumaru,Short Stack,Sigmar One,Signika,Signika Negative,Sirin Stencil,Six Caps,Slackey,Smokum,Smythe,Sniglet,Snippet,Sofia,Sonsie One,Sorts Mill Goudy,Special Elite,Spicy Rice,Spinnaker,Spirax,Squada One,Stardos Stencil,Stint Ultra Condensed,Stint Ultra Expanded,Stoke,Sue Ellen Francisco,Sunshiney,Supermercado One,Swanky and Moo Moo,Syncopate,Tangerine,Telex,Tenor Sans,Terminal Dosis,The Girl Next Door,Tienne,Tinos,Titan One,Trade Winds,Trochut,Trykker,Tulpen One,Ubuntu,Ubuntu Condensed,Ubuntu Mono,Ultra,Uncial Antiqua,UnifrakturCook,UnifrakturMaguntia,Unkempt,Unlock,Unna,VT323,Varela,Varela Round,Vast Shadow,Vibur,Vidaloka,Viga,Volkhov,Vollkorn,Voltaire,Waiting for the Sunrise,Wallpoet,Walter Turncoat,Wellfleet,Wire One,Yanone Kaffeesatz,Yellowtail,Yeseva One,Yesteryear,Zeyada";

      $fonts = explode(',', $gfonts447);
      $options = array(
          array(
              'value' => '',
              'label' => '- Please select -',
          )
      );
      foreach ($fonts as $f ){
          $options[] = array(
              'value' => $f,
              'label' => $f,
          );
      }

      return $options;
  }

}