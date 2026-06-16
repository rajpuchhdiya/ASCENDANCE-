<?php
/**
 * Programmatic ACF Fields Registration Class
 *
 * @package Ascendance\Core
 */

namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ACF_Fields {

    /**
     * Singleton instance
     * @var ACF_Fields|null
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Class constructor
     */
    private function __construct() {
        add_action( 'acf/init', array( $this, 'register_field_groups' ) );
    }

    /**
     * Register ACF Field Groups via PHP
     */
    public function register_field_groups() {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) {
            return; // ACF is not active
        }

        // 1. Brief Field Group
        acf_add_local_field_group( array(
            'key' => 'group_ascendance_brief',
            'title' => __( 'Intelligence Brief Metadata', 'ascendance-core' ),
            'fields' => array(
                array(
                    'key' => 'field_brief_subhead',
                    'label' => __( 'Subhead', 'ascendance-core' ),
                    'name' => 'subhead',
                    'type' => 'text',
                    'instructions' => __( 'Italic dek below the headline.', 'ascendance-core' ),
                    'required' => 0,
                ),
                array(
                    'key' => 'field_brief_analytical_claim',
                    'label' => __( 'Analytical Claim', 'ascendance-core' ),
                    'name' => 'analytical_claim',
                    'type' => 'textarea',
                    'instructions' => __( 'The primary forward-looking claim or core thesis of this brief.', 'ascendance-core' ),
                    'required' => 1,
                ),
                array(
                    'key' => 'field_brief_public_excerpt',
                    'label' => __( 'Public Excerpt', 'ascendance-core' ),
                    'name' => 'public_excerpt',
                    'type' => 'textarea',
                    'instructions' => __( 'Visible to non-subscribers, the AI citation surface (50-80 words).', 'ascendance-core' ),
                    'required' => 1,
                ),
                array(
                    'key' => 'field_brief_executive_summary',
                    'label' => __( 'Executive Summary', 'ascendance-core' ),
                    'name' => 'executive_summary',
                    'type' => 'textarea',
                    'instructions' => __( 'A high-level summary of the brief, visible to public/lower-tier users as a preview.', 'ascendance-core' ),
                    'required' => 1,
                    'rows' => 3,
                ),
                array(
                    'key' => 'field_brief_key_findings',
                    'label' => __( 'Key Findings', 'ascendance-core' ),
                    'name' => 'key_findings',
                    'type' => 'wysiwyg',
                    'instructions' => __( 'Key findings bullet points, data, and critical details.', 'ascendance-core' ),
                    'required' => 1,
                ),
                array(
                    'key' => 'field_brief_key_takeaways',
                    'label' => __( 'Key Takeaways', 'ascendance-core' ),
                    'name' => 'key_takeaways',
                    'type' => 'repeater',
                    'instructions' => __( '3-5 items: one text line each. Renders as a callout block at top.', 'ascendance-core' ),
                    'required' => 0,
                    'button_label' => __( 'Add Takeaway', 'ascendance-core' ),
                    'sub_fields' => array(
                        array(
                            'key' => 'field_brief_takeaway_text',
                            'label' => __( 'Takeaway', 'ascendance-core' ),
                            'name' => 'takeaway',
                            'type' => 'text',
                            'required' => 1,
                        ),
                    ),
                ),
                array(
                    'key' => 'field_brief_source_references',
                    'label' => __( 'Source References', 'ascendance-core' ),
                    'name' => 'source_references',
                    'type' => 'repeater',
                    'instructions' => __( 'Verify claims by citing specific documents, intelligence wires, or reports.', 'ascendance-core' ),
                    'required' => 0,
                    'button_label' => __( 'Add Reference', 'ascendance-core' ),
                    'sub_fields' => array(
                        array(
                            'key' => 'field_source_name',
                            'label' => __( 'Source / Publication Name', 'ascendance-core' ),
                            'name' => 'name',
                            'type' => 'text',
                            'required' => 1,
                        ),
                        array(
                            'key' => 'field_source_url',
                            'label' => __( 'URL', 'ascendance-core' ),
                            'name' => 'url',
                            'type' => 'url',
                            'required' => 0,
                        ),
                    ),
                ),
                array(
                    'key' => 'field_brief_sources',
                    'label' => __( 'Sources', 'ascendance-core' ),
                    'name' => 'sources',
                    'type' => 'repeater',
                    'instructions' => __( 'Detailed sources layout (source_name, source_url, source_date).', 'ascendance-core' ),
                    'required' => 0,
                    'button_label' => __( 'Add Source Details', 'ascendance-core' ),
                    'sub_fields' => array(
                        array(
                            'key' => 'field_brief_source_name',
                            'label' => __( 'Source Name', 'ascendance-core' ),
                            'name' => 'source_name',
                            'type' => 'text',
                            'required' => 1,
                        ),
                        array(
                            'key' => 'field_brief_source_url',
                            'label' => __( 'Source URL', 'ascendance-core' ),
                            'name' => 'source_url',
                            'type' => 'url',
                            'required' => 0,
                        ),
                        array(
                            'key' => 'field_brief_source_date',
                            'label' => __( 'Source Date', 'ascendance-core' ),
                            'name' => 'source_date',
                            'type' => 'date_picker',
                            'required' => 0,
                            'display_format' => 'F j, Y',
                            'return_format' => 'Y-m-d',
                        ),
                    ),
                ),
                array(
                    'key' => 'field_brief_related_briefs',
                    'label' => __( 'Related Briefs', 'ascendance-core' ),
                    'name' => 'related_briefs',
                    'type' => 'relationship',
                    'instructions' => __( 'Cross-reference supporting intelligence briefs for details (max 5).', 'ascendance-core' ),
                    'required' => 0,
                    'post_type' => array( 'brief' ),
                    'filters' => array( 'search' ),
                    'max' => 5,
                    'return_format' => 'object',
                ),
                array(
                    'key' => 'field_brief_version',
                    'label' => __( 'Brief Version', 'ascendance-core' ),
                    'name' => 'brief_version',
                    'type' => 'number',
                    'instructions' => __( 'Increments on substantive revision.', 'ascendance-core' ),
                    'required' => 1,
                    'default_value' => 1,
                ),
                array(
                    'key' => 'field_brief_ai_generated',
                    'label' => __( 'AI Generated?', 'ascendance-core' ),
                    'name' => 'ai_generated',
                    'type' => 'true_false',
                    'instructions' => __( 'True if any portion came from AI Studio.', 'ascendance-core' ),
                    'message' => __( 'Yes, partially/fully AI generated', 'ascendance-core' ),
                    'default_value' => 0,
                    'ui' => 1,
                ),
                array(
                    'key' => 'field_brief_tier_access',
                    'label' => __( 'Tier Access Level', 'ascendance-core' ),
                    'name' => 'tier_access',
                    'type' => 'select',
                    'instructions' => __( 'The minimum membership tier required to unlock this intelligence brief.', 'ascendance-core' ),
                    'required' => 1,
                    'choices' => array(
                        'essential'    => __( 'Essential (Tier 1)', 'ascendance-core' ),
                        'professional' => __( 'Professional (Tier 2)', 'ascendance-core' ),
                        'enterprise'   => __( 'Enterprise (Tier 3)', 'ascendance-core' ),
                    ),
                    'default_value' => 'essential',
                    'ui' => 1,
                ),
                array(
                    'key' => 'field_brief_featured_flag',
                    'label' => __( 'Featured Post?', 'ascendance-core' ),
                    'name' => 'featured_flag',
                    'type' => 'true_false',
                    'instructions' => __( 'Feature this brief prominently on the main subscriber dashboard.', 'ascendance-core' ),
                    'message' => __( 'Yes, display as featured', 'ascendance-core' ),
                    'default_value' => 0,
                    'ui' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'brief',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
        ) );

        // 2. Update Field Group
        acf_add_local_field_group( array(
            'key' => 'group_ascendance_update',
            'title' => __( 'Intelligence Update Metadata', 'ascendance-core' ),
            'fields' => array(
                array(
                    'key' => 'field_update_parent_brief',
                    'label' => __( 'Parent Intelligence Brief', 'ascendance-core' ),
                    'name' => 'parent_brief',
                    'type' => 'post_object',
                    'instructions' => __( 'Link this dynamic update to its parent intelligence dossier or brief.', 'ascendance-core' ),
                    'required' => 1,
                    'post_type' => array( 'brief', 'dossier' ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'return_format' => 'id',
                    'ui' => 1,
                ),
                array(
                    'key' => 'field_update_date',
                    'label' => __( 'Update Date', 'ascendance-core' ),
                    'name' => 'update_date',
                    'type' => 'date_picker',
                    'instructions' => __( 'The development date (not the publish date).', 'ascendance-core' ),
                    'required' => 1,
                    'display_format' => 'F j, Y',
                    'return_format' => 'Y-m-d',
                ),
                array(
                    'key' => 'field_update_one_line_summary',
                    'label' => __( 'One Line Summary', 'ascendance-core' ),
                    'name' => 'one_line_summary',
                    'type' => 'text',
                    'instructions' => __( 'Max 160 characters. Renders as meta description.', 'ascendance-core' ),
                    'required' => 1,
                    'maxlength' => 160,
                ),
                array(
                    'key' => 'field_update_impact_assessment',
                    'label' => __( 'Impact Assessment', 'ascendance-core' ),
                    'name' => 'impact_assessment',
                    'type' => 'select',
                    'required' => 1,
                    'choices' => array(
                        'low'      => __( 'Low - Minor adjustments only', 'ascendance-core' ),
                        'medium'   => __( 'Medium - Notable shifting variables', 'ascendance-core' ),
                        'high'     => __( 'High - Major trend disruption', 'ascendance-core' ),
                        'critical' => __( 'Critical - Dynamic realignment required', 'ascendance-core' ),
                    ),
                    'default_value' => 'medium',
                    'ui' => 1,
                ),
                array(
                    'key' => 'field_update_key_update',
                    'label' => __( 'Key Update Content', 'ascendance-core' ),
                    'name' => 'key_update',
                    'type' => 'wysiwyg',
                    'instructions' => __( 'The latest dynamic developments and changes.', 'ascendance-core' ),
                    'required' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'update',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
        ) );

        // 3. Dossier Field Group
        acf_add_local_field_group( array(
            'key' => 'group_ascendance_dossier',
            'title' => __( 'Intelligence Dossier Metadata', 'ascendance-core' ),
            'fields' => array(
                array(
                    'key' => 'field_dossier_subhead',
                    'label' => __( 'Subhead', 'ascendance-core' ),
                    'name' => 'subhead',
                    'type' => 'text',
                    'instructions' => __( 'Italic dek below the headline.', 'ascendance-core' ),
                    'required' => 0,
                ),
                array(
                    'key' => 'field_dossier_analytical_claim',
                    'label' => __( 'Analytical Claim', 'ascendance-core' ),
                    'name' => 'analytical_claim',
                    'type' => 'textarea',
                    'instructions' => __( 'The primary forward-looking claim or core thesis of this dossier.', 'ascendance-core' ),
                    'required' => 1,
                ),
                array(
                    'key' => 'field_dossier_public_excerpt',
                    'label' => __( 'Public Excerpt', 'ascendance-core' ),
                    'name' => 'public_excerpt',
                    'type' => 'textarea',
                    'instructions' => __( 'Visible to non-subscribers, the AI citation surface (50-80 words).', 'ascendance-core' ),
                    'required' => 1,
                ),
                array(
                    'key' => 'field_dossier_executive_summary',
                    'label' => __( 'Executive Summary', 'ascendance-core' ),
                    'name' => 'executive_summary',
                    'type' => 'textarea',
                    'required' => 1,
                    'rows' => 4,
                ),
                array(
                    'key' => 'field_dossier_key_findings',
                    'label' => __( 'Key Findings', 'ascendance-core' ),
                    'name' => 'key_findings',
                    'type' => 'wysiwyg',
                    'instructions' => __( 'Key findings bullet points, data, and critical details.', 'ascendance-core' ),
                    'required' => 1,
                ),
                array(
                    'key' => 'field_dossier_key_takeaways',
                    'label' => __( 'Key Takeaways', 'ascendance-core' ),
                    'name' => 'key_takeaways',
                    'type' => 'repeater',
                    'instructions' => __( '3-5 items: one text line each. Renders as a callout block at top.', 'ascendance-core' ),
                    'required' => 0,
                    'button_label' => __( 'Add Takeaway', 'ascendance-core' ),
                    'sub_fields' => array(
                        array(
                            'key' => 'field_dossier_takeaway_text',
                            'label' => __( 'Takeaway', 'ascendance-core' ),
                            'name' => 'takeaway',
                            'type' => 'text',
                            'required' => 1,
                        ),
                    ),
                ),
                array(
                    'key' => 'field_dossier_source_references',
                    'label' => __( 'Source References', 'ascendance-core' ),
                    'name' => 'source_references',
                    'type' => 'repeater',
                    'instructions' => __( 'Verify claims by citing specific documents, intelligence wires, or reports.', 'ascendance-core' ),
                    'required' => 0,
                    'button_label' => __( 'Add Reference', 'ascendance-core' ),
                    'sub_fields' => array(
                        array(
                            'key' => 'field_dossier_source_name_ref',
                            'label' => __( 'Source / Publication Name', 'ascendance-core' ),
                            'name' => 'name',
                            'type' => 'text',
                            'required' => 1,
                        ),
                        array(
                            'key' => 'field_dossier_source_url_ref',
                            'label' => __( 'URL', 'ascendance-core' ),
                            'name' => 'url',
                            'type' => 'url',
                            'required' => 0,
                        ),
                    ),
                ),
                array(
                    'key' => 'field_dossier_sources',
                    'label' => __( 'Sources', 'ascendance-core' ),
                    'name' => 'sources',
                    'type' => 'repeater',
                    'instructions' => __( 'Detailed sources layout (source_name, source_url, source_date).', 'ascendance-core' ),
                    'required' => 0,
                    'button_label' => __( 'Add Source Details', 'ascendance-core' ),
                    'sub_fields' => array(
                        array(
                            'key' => 'field_dossier_source_name',
                            'label' => __( 'Source Name', 'ascendance-core' ),
                            'name' => 'source_name',
                            'type' => 'text',
                            'required' => 1,
                        ),
                        array(
                            'key' => 'field_dossier_source_url',
                            'label' => __( 'Source URL', 'ascendance-core' ),
                            'name' => 'source_url',
                            'type' => 'url',
                            'required' => 0,
                        ),
                        array(
                            'key' => 'field_dossier_source_date',
                            'label' => __( 'Source Date', 'ascendance-core' ),
                            'name' => 'source_date',
                            'type' => 'date_picker',
                            'required' => 0,
                            'display_format' => 'F j, Y',
                            'return_format' => 'Y-m-d',
                        ),
                    ),
                ),
                array(
                    'key' => 'field_dossier_related_briefs',
                    'label' => __( 'Related Briefs', 'ascendance-core' ),
                    'name' => 'related_briefs',
                    'type' => 'relationship',
                    'instructions' => __( 'Cross-reference supporting intelligence briefs for details (max 5).', 'ascendance-core' ),
                    'required' => 0,
                    'post_type' => array( 'brief' ),
                    'filters' => array( 'search', 'taxonomy' ),
                    'max' => 5,
                    'return_format' => 'object',
                ),
                array(
                    'key' => 'field_dossier_brief_version',
                    'label' => __( 'Brief Version', 'ascendance-core' ),
                    'name' => 'brief_version',
                    'type' => 'number',
                    'instructions' => __( 'Increments on substantive revision.', 'ascendance-core' ),
                    'required' => 1,
                    'default_value' => 1,
                ),
                array(
                    'key' => 'field_dossier_ai_generated',
                    'label' => __( 'AI Generated?', 'ascendance-core' ),
                    'name' => 'ai_generated',
                    'type' => 'true_false',
                    'instructions' => __( 'True if any portion came from AI Studio.', 'ascendance-core' ),
                    'message' => __( 'Yes, partially/fully AI generated', 'ascendance-core' ),
                    'default_value' => 0,
                    'ui' => 1,
                ),
                array(
                    'key' => 'field_dossier_tier_access',
                    'label' => __( 'Tier Access Level', 'ascendance-core' ),
                    'name' => 'tier_access',
                    'type' => 'select',
                    'required' => 1,
                    'choices' => array(
                        'essential'    => __( 'Essential (Tier 1)', 'ascendance-core' ),
                        'professional' => __( 'Professional (Tier 2)', 'ascendance-core' ),
                        'enterprise'   => __( 'Enterprise (Tier 3)', 'ascendance-core' ),
                    ),
                    'default_value' => 'professional', // Dossiers default to professional tier
                    'ui' => 1,
                ),
                array(
                    'key' => 'field_dossier_featured_flag',
                    'label' => __( 'Featured Post?', 'ascendance-core' ),
                    'name' => 'featured_flag',
                    'type' => 'true_false',
                    'instructions' => __( 'Feature this brief prominently on the main subscriber dashboard.', 'ascendance-core' ),
                    'message' => __( 'Yes, display as featured', 'ascendance-core' ),
                    'default_value' => 0,
                    'ui' => 1,
                ),
                // Dossier Specific Fields
                array(
                    'key' => 'field_dossier_download_pdf',
                    'label' => __( 'Download Dossier PDF', 'ascendance-core' ),
                    'name' => 'download_pdf',
                    'type' => 'file',
                    'instructions' => __( 'Upload the complete high-density intelligence dossier PDF report.', 'ascendance-core' ),
                    'required' => 0,
                    'return_format' => 'array',
                    'mime_types' => 'pdf',
                ),
                array(
                    'key' => 'field_dossier_stakeholders',
                    'label' => __( 'Key Stakeholders Involved', 'ascendance-core' ),
                    'name' => 'stakeholders',
                    'type' => 'repeater',
                    'instructions' => __( 'Key corporations, political institutions, or actors tracked in this dossier.', 'ascendance-core' ),
                    'required' => 0,
                    'layout' => 'block',
                    'button_label' => __( 'Add Stakeholder', 'ascendance-core' ),
                    'sub_fields' => array(
                        array(
                            'key' => 'field_stakeholder_name',
                            'label' => __( 'Stakeholder Name', 'ascendance-core' ),
                            'name' => 'name',
                            'type' => 'text',
                            'required' => 1,
                        ),
                        array(
                            'key' => 'field_stakeholder_role',
                            'label' => __( 'Role / Organization', 'ascendance-core' ),
                            'name' => 'role',
                            'type' => 'text',
                            'required' => 1,
                        ),
                    ),
                ),
                array(
                    'key' => 'field_dossier_last_revised',
                    'label' => __( 'Last Revised Date', 'ascendance-core' ),
                    'name' => 'last_revised',
                    'type' => 'date_picker',
                    'instructions' => __( 'Auto-updated on save or set manually.', 'ascendance-core' ),
                    'required' => 0,
                    'display_format' => 'F j, Y',
                    'return_format' => 'Y-m-d',
                ),
                array(
                    'key' => 'field_dossier_revision_log',
                    'label' => __( 'Revision Log', 'ascendance-core' ),
                    'name' => 'revision_log',
                    'type' => 'repeater',
                    'instructions' => __( 'Track changes over time (revision_date + revision_summary).', 'ascendance-core' ),
                    'required' => 0,
                    'button_label' => __( 'Add Revision Entry', 'ascendance-core' ),
                    'sub_fields' => array(
                        array(
                            'key' => 'field_dossier_rev_date',
                            'label' => __( 'Revision Date', 'ascendance-core' ),
                            'name' => 'revision_date',
                            'type' => 'date_picker',
                            'required' => 1,
                            'display_format' => 'F j, Y',
                            'return_format' => 'Y-m-d',
                        ),
                        array(
                            'key' => 'field_dossier_rev_summary',
                            'label' => __( 'Revision Summary', 'ascendance-core' ),
                            'name' => 'revision_summary',
                            'type' => 'textarea',
                            'required' => 1,
                            'rows' => 2,
                        ),
                    ),
                ),
                array(
                    'key' => 'field_dossier_data_blocks',
                    'label' => __( 'Data Blocks', 'ascendance-core' ),
                    'name' => 'data_blocks',
                    'type' => 'repeater',
                    'instructions' => __( 'Embedded data blocks (block_title + structured rows).', 'ascendance-core' ),
                    'required' => 0,
                    'button_label' => __( 'Add Data Block', 'ascendance-core' ),
                    'sub_fields' => array(
                        array(
                            'key' => 'field_dossier_block_title',
                            'label' => __( 'Block Title', 'ascendance-core' ),
                            'name' => 'block_title',
                            'type' => 'text',
                            'required' => 1,
                        ),
                        array(
                            'key' => 'field_dossier_block_rows',
                            'label' => __( 'Structured Rows', 'ascendance-core' ),
                            'name' => 'structured_rows',
                            'type' => 'repeater',
                            'required' => 0,
                            'button_label' => __( 'Add Row', 'ascendance-core' ),
                            'sub_fields' => array(
                                array(
                                    'key' => 'field_dossier_col_1',
                                    'label' => __( 'Label / Key', 'ascendance-core' ),
                                    'name' => 'column_1',
                                    'type' => 'text',
                                    'required' => 1,
                                ),
                                array(
                                    'key' => 'field_dossier_col_2',
                                    'label' => __( 'Value / Data', 'ascendance-core' ),
                                    'name' => 'column_2',
                                    'type' => 'text',
                                    'required' => 0,
                                ),
                                array(
                                    'key' => 'field_dossier_col_3',
                                    'label' => __( 'Metadata / Notes', 'ascendance-core' ),
                                    'name' => 'column_3',
                                    'type' => 'text',
                                    'required' => 0,
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_dossier_companion_downloads',
                    'label' => __( 'Companion Downloads', 'ascendance-core' ),
                    'name' => 'companion_downloads',
                    'type' => 'repeater',
                    'instructions' => __( 'PDF snapshots at major revisions.', 'ascendance-core' ),
                    'required' => 0,
                    'button_label' => __( 'Add PDF Snapshot', 'ascendance-core' ),
                    'sub_fields' => array(
                        array(
                            'key' => 'field_dossier_comp_file',
                            'label' => __( 'PDF File', 'ascendance-core' ),
                            'name' => 'file',
                            'type' => 'file',
                            'required' => 1,
                            'return_format' => 'array',
                            'mime_types' => 'pdf',
                        ),
                        array(
                            'key' => 'field_dossier_comp_label',
                            'label' => __( 'Label (e.g. Version 2.1 PDF)', 'ascendance-core' ),
                            'name' => 'label',
                            'type' => 'text',
                            'required' => 1,
                        ),
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'dossier',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
        ) );
    }
}
