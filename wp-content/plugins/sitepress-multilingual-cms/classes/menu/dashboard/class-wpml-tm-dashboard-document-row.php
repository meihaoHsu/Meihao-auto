<?php

use WPML\FP\Obj;
use WPML\TM\Menu\Dashboard\PostJobsRepository;
use WPML\TM\API\Jobs;

class WPML_TM_Dashboard_Document_Row {

	/** @var stdClass $data */
	private $data;
	private $post_types;
	private $active_languages;
	private $selected;
	private $note_text;
	private $note_icon_class;
	private $post_statuses;
	/** @var SitePress $sitepress */
	private $sitepress;
	/** @var WPML_TM_Translatable_Element_Provider $translatable_element_provider */
	private $translatable_element_provider;

	public function __construct(
		$doc_data,
		$post_types,
		$post_statuses,
		$active_languages,
		$selected,
		SitePress $sitepress,
		WPML_TM_Translatable_Element_Provider $translatable_element_provider
	) {
		$this->data                          = $doc_data;
		$this->post_statuses                 = $post_statuses;
		$this->selected                      = $selected;
		$this->post_types                    = $post_types;
		$this->active_languages              = $active_languages;
		$this->sitepress                     = $sitepress;
		$this->translatable_element_provider = $translatable_element_provider;
	}

	public function get_word_count() {
		$current_document = $this->data;
		$type             = 'post';

		if ( $this->is_external_type() ) {
			$type = 'package';
		}

		$translatable_element = $this->translatable_element_provider->get_from_type( $type, $current_document->ID );

		return apply_filters(
			'wpml_tm_estimated_words_count',
			$translatable_element->get_words_count(),
			$current_document
		);
	}

	public function get_title() {
		return $this->data->title ? $this->data->title : __( '(missing title)', 'sitepress' );
	}

	private function is_external_type() {
		$doc = $this->data;

		return strpos( $doc->translation_element_type, 'post_' ) !== 0;
	}

	public function get_type_prefix() {
		$type = $this->data->translation_element_type;
		$type = explode( '_', $type );
		if ( count( $type ) > 1 ) {
			$type = $type[0];
		}

		return $type;
	}

	public function get_type() {
		$type = $this->data->translation_element_type;
		$type = explode( '_', $type );
		if ( count( $type ) > 1 ) {
			unset( $type[0] );
		}
		$type = join( '_', $type );

		return $type;
	}

	public function display() {
		global $iclTranslationManagement;
		$current_document  = $this->data;
		$count             = $this->get_word_count();
		$post_actions      = array();
		$post_actions_link = '';
		$element_type      = $this->get_type_prefix();
		$check_field_name  = $element_type;
		$post_title        = $this->get_title();
		$post_status       = $this->get_general_status() === $this->post_statuses['draft'] ? ' — ' . $this->post_statuses['draft'] : '';

		$post_view_link = '';
		$post_edit_link = '';
		if ( ! $this->is_external_type() ) {
			$post_link_factory = new WPML_TM_Post_Link_Factory( $this->sitepress );
			$post_edit_link    = $post_link_factory->edit_link_anchor( $current_document->ID, __( 'Edit', 'sitepress' ) );
			$post_view_link    = $post_link_factory->view_link_anchor( $current_document->ID, __( 'View', 'sitepress' ) );
		}

		$jobs = ( new PostJobsRepository() )->getJobsGroupedByLang( $current_document->ID, $element_type );

		$post_edit_link = apply_filters( 'wpml_document_edit_item_link', $post_edit_link, __( 'Edit', 'sitepress' ), $current_document, $element_type, $this->get_type() );
		if ( $post_edit_link ) {
			$post_actions[] = "<span class='edit'>" . $post_edit_link . '</span>';
		}

		$post_view_link = apply_filters( 'wpml_document_view_item_link', $post_view_link, __( 'View', 'sitepress' ), $current_document, $element_type, $this->get_type() );
		if ( $post_view_link && ! in_array( $this->get_type(), [ 'wp_template_part', 'wp_template', 'wp_navigation' ] ) ) {
			$post_actions[] = "<span class='view'>" . $post_view_link . '</span>';
		}

		if ( $post_actions ) {
			$post_actions_link .= '<div class="row-actions">' . implode( ' | ', $post_actions ) . '</div>';
		}

		$row_data     = apply_filters( 'wpml_translation_dashboard_row_data', array( 'word_count' => $count ), $this->data );
		$row_data_str = '';
		foreach ( $row_data as $key => $value ) {
			$row_data_str .= 'data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
		}
		?>
		<tr id="row_<?php echo sanitize_html_class( $current_document->ID ); ?>" <?php echo $row_data_str; ?>>
			<td scope="row">
				<?php
				$checked      = checked( true, isset( $_GET['post_id'] ) || $this->selected, false );
				$tooltip_link = 'https://wpml.org/documentation/translating-your-contents/using-different-translation-editors-for-different-pages/?utm_source=plugin&utm_medium=gui&utm_campaign=wpmltm';

				/* translators: %1$s: opening <a> tag, %2$s: closing </a> tag. */
				$tooltip_content = sprintf( __( 'This page is set to be %1$stranslated manually%2$s using the WordPress Editor', 'sitepress' ), '<a href="' . $tooltip_link . '">', '</a>' );

				$name  = $check_field_name . '[' . $current_document->ID . '][checked]';
				$value = $current_document->ID;

				$originalElement          = \WPML\Element\API\Translations::getOriginal( $current_document->ID, $current_document->translation_element_type );
				$documentOriginalLangCode = Obj::propOr( false, 'language_code', $originalElement );
				$isOriginalInPrimaryLang  = (int) ( $documentOriginalLangCode === \WPML\Element\API\Languages::getDefaultCode() );
				?>
				<?php
				if ( Obj::propOr( false, 'is_blocked_by_filter', $current_document ) ) {
					?>
					<div class="js-otgs-popover-tooltip"
						 data-tippy-content="<?php echo esc_attr( $tooltip_content ); ?>">
						<input
							type="checkbox"
							disabled="disabled"
							data-wpml-is-original-in-primary-lang="<?php echo $isOriginalInPrimaryLang ?>"
							data-wpml-original-lang-code="<?php echo $documentOriginalLangCode ?>"
							value="<?php echo esc_attr( $value ); ?>"
							name="<?php echo esc_attr( $name ); ?>"
							<?php /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
							echo $checked; ?> />
					</div>
					<?php
				} else {
					?>
					<input
						type="checkbox"
						data-wpml-is-original-in-primary-lang="<?php echo $isOriginalInPrimaryLang ?>"
						data-wpml-original-lang-code="<?php echo $documentOriginalLangCode ?>"
						value="<?php echo esc_attr( $value ); ?>"
						name="<?php echo esc_attr( $name ); ?>"
						<?php /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */
						echo $checked; ?> />
					<?php
				}
				?>
				<input type="hidden" value="<?php echo $element_type; ?>" name="<?php echo $check_field_name; ?>[<?php echo $current_document->ID; ?>][type]"/>
			</td>
			<td scope="row" class="post-title column-title">
				<?php
				echo esc_html( $post_title );
				echo esc_html( $post_status );
				echo $post_actions_link;
				?>
				<div class="icl_post_note" id="icl_post_note_<?php echo $current_document->ID; ?>">
					<?php
					$note = '';
					if ( ! $current_document->is_translation ) {
						$note            = WPML_TM_Translator_Note::get( $current_document->ID );
						$this->note_text = '';
						if ( $note ) {
							$this->note_text       = __( 'Edit note for the translators', 'sitepress' );
							$this->note_icon_class = 'otgs-ico-note-edit-o';
						} else {
							$this->note_text       = __( 'Add note for the translators', 'sitepress' );
							$this->note_icon_class = 'otgs-ico-note-add-o';
						}
					}
					?>
					<label for="post_note_<?php echo $current_document->ID; ?>">
						<?php _e( 'Note for the translators', 'sitepress' ); ?>
					</label>
					<textarea id="post_note_<?php echo $current_document->ID; ?>" rows="5"><?php echo $note; ?></textarea>
					<table width="100%">
						<tr>
							<td style="border-bottom:none">
								<input type="button" class="icl_tn_cancel button" value="<?php _e( 'Cancel', 'sitepress' ); ?>" />
								<input class="icl_tn_post_id" type="hidden" value="<?php echo $current_document->ID; ?>"/>
							</td>
							<td align="right" style="border-bottom:none">
								<input type="button" class="icl_tn_save button-primary" value="<?php _e( 'Save', 'sitepress' ); ?>"/>
							</td>
						</tr>
					</table>
				</div>
			</td>
			<td scope="row" class="manage-column wpml-column-type">
				<?php
				if ( isset( $this->post_types[ $this->get_type() ] ) ) {
					$custom_post_type_labels = $this->post_types[ $this->get_type() ]->labels;
					if ( $custom_post_type_labels->singular_name != '' ) {
						echo $custom_post_type_labels->singular_name;
					} else {
						echo $custom_post_type_labels->name;
					}
				} else {
					echo $this->get_type();
				}
				?>
			</td>
			<td scope="row" class="manage-column column-active-languages wpml-col-languages">
				<?php
				$has_jobs_in_progress = false;

				foreach ( $this->active_languages as $code => $lang ) {
					if ( $code == $this->data->language_code ) {
						continue;
					}

					$needsReview = false;
					if ( isset( $jobs[ $code ] ) ) {
						$job            = $jobs[ $code ];
						$status         = $job['status'] ?: $this->get_status_in_lang( $code );
						$job_entity_id  = $job['entity_id'];
						$job_id         = $job['job_id'];
						$needsReview    = $job['needsReview'];
						$automatic      = $job['automatic'];
						$isLocal        = $job['isLocal'];
						$shouldBeSynced = Jobs::shouldBeATESynced( $job );
					} else {
						$status         = $this->get_status_in_lang( $code );
						$job_entity_id  = 0;
						$job_id         = 0;
						$automatic      = false;
						$shouldBeSynced = false;
						$isLocal        = true;
					}

					if ( $needsReview ) {
						$translation_status_text = esc_attr( __( 'Needs review', 'sitepress' ) );
					} else {
						switch ( $status ) {
							case ICL_TM_NOT_TRANSLATED:
								$translation_status_text = esc_attr( __( 'Not translated', 'sitepress' ) );
								break;
							case ICL_TM_WAITING_FOR_TRANSLATOR:
								$translation_status_text = $automatic
									? esc_attr( __( 'Waiting for automatic translation', 'sitepress' ) )
									: esc_attr( __( 'Waiting for translator', 'sitepress' ) );
								break;
							case ICL_TM_IN_BASKET:
								$translation_status_text = esc_attr( __( 'In basket', 'sitepress' ) );
								break;
							case ICL_TM_IN_PROGRESS:
								if ( $automatic ) {
									$translation_status_text = esc_attr( __( 'Waiting for automatic translation', 'sitepress' ) );
								} elseif ( $isLocal ) {
									$translation_status_text = esc_attr( __( 'Waiting for translator', 'sitepress' ) );
								} else {
									$translation_status_text = esc_attr( __( 'Waiting for translation service', 'sitepress' ) );
								}

								$has_jobs_in_progress = true;
								break;
							case ICL_TM_TRANSLATION_READY_TO_DOWNLOAD:
								$translation_status_text = esc_attr(
									__(
										'Translation ready to download',
										'sitepress'
									)
								);
								$has_jobs_in_progress    = true;
								break;
							case ICL_TM_DUPLICATE:
								$translation_status_text = esc_attr( __( 'Duplicate of default language', 'sitepress' ) );
								break;
							case ICL_TM_COMPLETE:
								$translation_status_text = esc_attr( __( 'Translation completed', 'sitepress' ) );
								break;
							case ICL_TM_NEEDS_UPDATE:
								$translation_status_text = esc_attr( __( 'Needs update', 'sitepress' ) );
								break;
							case ICL_TM_ATE_NEEDS_RETRY:
								$translation_status_text = esc_attr( __( 'In progress', 'sitepress' ) ) . ' - ' . esc_attr( __( 'needs retry', 'sitepress' ) );
								break;
							default:
								$translation_status_text = '';
						}
					}


					$status_icon_class = $iclTranslationManagement->status2icon_class( $status, ICL_TM_NEEDS_UPDATE === (int) $status, $needsReview );
					?>

					<span data-document_status="<?php echo $status; ?>"
						  id="wpml-job-status-<?php echo esc_attr( $job_entity_id ); ?>"
						  class="js-wpml-translate-link"
						  data-tm-job-id="<?php echo esc_attr( $job_id ); ?>"
						  data-automatic="<?php echo esc_attr( $automatic ); ?>"
                          data-wpmlcc-lang="<?php echo esc_attr( $code ); ?>"
                          data-should-ate-sync="<?php echo $shouldBeSynced ? '1' : '0' ?>"
					>
						<i class="<?php echo esc_attr( $status_icon_class ); ?> js-otgs-popover-tooltip"
						   title="<?php echo esc_attr( $lang['display_name'] ); ?>: <?php echo $translation_status_text; ?>"
						   data-original-title="<?php echo esc_attr( $lang['display_name'] ); ?>: <?php echo $translation_status_text; ?>"
						></i>
					</span>
					<?php
				}
				?>
			</td>
			<td scope="row" class="post-date column-date">
				<?php
				$element_date = $this->get_date();
				if ( $element_date ) {
					echo date( 'Y-m-d', strtotime( $element_date ) );
				}
				echo '<br />';
				echo $this->get_general_status();
				?>
			</td>
			<td class="column-actions" scope="row" >
				<?php
				if ( ! $current_document->is_translation ) {
					?>
					<a title="<?php echo $this->note_text; ?>" href="#" class="icl_tn_link" id="icl_tn_link_<?php echo $current_document->ID; ?>" >
						<i class="<?php echo $this->note_icon_class; ?>"></i>
					</a>

					<?php
					if ( $has_jobs_in_progress && $this->has_remote_jobs( $jobs ) ) {
						?>
						<a class="otgs-ico-refresh wpml-sync-and-download-translation"
						   data-element-id="<?php echo $current_document->ID; ?>"
						   data-element-type="<?php echo esc_attr( $element_type ); ?>"
						   data-jobs="<?php echo htmlspecialchars( (string) json_encode( array_values( $jobs ) ) ); ?>"
						   data-icons="
						   <?php
							echo htmlspecialchars(
								(string) json_encode(
									array(
										'completed' => $iclTranslationManagement->status2icon_class( ICL_TM_COMPLETE, false ),
										'canceled'  => $iclTranslationManagement->status2icon_class( ICL_TM_NOT_TRANSLATED, false ),
										'progress'  => $iclTranslationManagement->status2icon_class( ICL_TM_IN_PROGRESS, false ),
									)
								)
							)
							?>
						   "
						   title="<?php esc_attr_e( 'Check status and get translations', 'sitepress' ); ?>"
						</a>
						<?php
					}
				}
				?>
			</td>
		</tr>
		<?php
	}

	private function get_date() {
		if ( ! $this->is_external_type() ) {
			/** @var WP_Post $post */
			$post = get_post( $this->data->ID );
			$date = get_post_time( 'U', false, $post );
		} else {
			$date = apply_filters(
				'wpml_tm_dashboard_date',
				time(),
				$this->data->ID,
				$this->data->translation_element_type
			);
		}
		$date = date( 'y-m-d', $date );

		return $date;
	}

	private function has_remote_jobs( $jobs ) {
		foreach ( $jobs as $job ) {
			if ( ! $job['isLocal'] ) {
				return true;
			}
		}

		return false;
	}

	private function get_general_status() {
		if ( ! $this->is_external_type() ) {
			$status      = get_post_status( $this->data->ID );
			$status_text = isset( $this->post_statuses[ $status ] ) ? $this->post_statuses[ $status ] : $status;
		} else {
			$status_text = apply_filters(
				'wpml_tm_dashboard_status',
				'external',
				$this->data->ID,
				$this->data->translation_element_type
			);
		}

		return $status_text;
	}

	private function get_status_in_lang( $language_code ) {
		$status_helper = wpml_get_post_status_helper();

		return $status_helper->get_status( false, $this->data->trid, $language_code );
	}
}
