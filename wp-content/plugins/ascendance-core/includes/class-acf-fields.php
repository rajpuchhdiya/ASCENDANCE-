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
                    'key' => 'field_brief_analytical_claim',
                    'label' => __( 'Analytical Claim', 'ascendance-core' ),
                    'name' => 'analytical_claim',
                    'type' => 'text',
                    'instructions' => __( 'The primary forward-looking claim or core thesis of this brief.', 'ascendance-core' ),
                    'required' => 1,
                    'placeholder' => __( 'e.g., Escalation in supply disruption triggers commodity spike...', 'ascendance-core' ),
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
                    'toolbar' => 'basic',
                    'media_upload' => 0,
                ),
                array(
                    'key' => 'field_brief_source_references',
                    'label' => __( 'Source References', 'ascendance-core' ),
                    'name' => 'source_references',
                    'type' => 'repeater',
                    'instructions' => __( 'Verify claims by citing specific documents, intelligence wires, or reports.', 'ascendance-core' ),
                    'required' => 0,
                    'collapsed' => 'field_source_name',
                    'min' => 0,
                    'layout' => 'table',
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
                    'post_type' => array( 'brief' ),
                    'allow_null' => 0,
                    'multiple' => 0,
                    'return_format' => 'id',
                    'ui' => 1,
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
                    'key' => 'field_dossier_executive_summary',
                    'label' => __( 'Executive Summary', 'ascendance-core' ),
                    'name' => 'executive_summary',
                    'type' => 'textarea',
                    'required' => 1,
                    'rows' => 4,
                ),
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
                    'key' => 'field_dossier_related_briefs',
                    'label' => __( 'Related Briefs', 'ascendance-core' ),
                    'name' => 'related_briefs',
                    'type' => 'relationship',
                    'instructions' => __( 'Cross-reference supporting intelligence briefs for details.', 'ascendance-core' ),
                    'required' => 0,
                    'post_type' => array( 'brief' ),
                    'filters' => array( 'search', 'taxonomy' ),
                    'return_format' => 'object',
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
