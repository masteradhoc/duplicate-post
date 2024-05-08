/* global duplicatePost, duplicatePostNotices */

import { useState } from 'react';
import { registerPlugin } from "@wordpress/plugins";
import { PluginDocumentSettingPanel, PluginPostStatusInfo } from "@wordpress/edit-post";
import { Fragment } from "@wordpress/element";
import { Button, ToggleControl } from '@wordpress/components';
import { __ } from "@wordpress/i18n";
import { select, subscribe, dispatch } from "@wordpress/data";
import { redirectOnSaveCompletion } from "./duplicate-post-functions";


class DuplicatePost {
	constructor() {
		this.renderNotices();
		this.removeSlugSidebarPanel();
	}

	/**
	 * Handles the redirect from the copy to the original.
	 *
	 * @returns {void}
	 */
	handleRedirect() {
		if ( ! parseInt( duplicatePost.rewriting, 10 ) ) {
			return;
		}

		let wasSavingPost      = false;
		let wasSavingMetaboxes = false;
		let wasAutoSavingPost  = false;

		/**
		 * Determines when the redirect needs to happen.
		 *
		 * @returns {void}
		 */
		subscribe( () => {
			if ( ! this.isSafeRedirectURL( duplicatePost.originalEditURL ) || ! this.isCopyAllowedToBeRepublished() ) {
				return;
			}

			const completed = redirectOnSaveCompletion( duplicatePost.originalEditURL, { wasSavingPost, wasSavingMetaboxes, wasAutoSavingPost } );

			wasSavingPost      = completed.isSavingPost;
			wasSavingMetaboxes = completed.isSavingMetaBoxes;
			wasAutoSavingPost  = completed.isAutosavingPost;
		} );
	}

	/**
	 * Checks whether the URL for the redirect from the copy to the original matches the expected format.
	 *
	 * Allows only URLs with a http(s) protocol, a pathname matching the admin
	 * post.php page and a parameter string with the expected parameters.
	 *
	 * @returns {bool} Whether the redirect URL matches the expected format.
	 */
	isSafeRedirectURL( url ) {
		const parser = document.createElement( 'a' );
		parser.href  = url;

		if (
			/^https?:$/.test( parser.protocol ) &&
			/\/wp-admin\/post\.php$/.test( parser.pathname ) &&
			/\?action=edit&post=[0-9]+&dprepublished=1&dpcopy=[0-9]+&dpnonce=[a-z0-9]+/i.test( parser.search )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Determines whether a Rewrite & Republish copy can be republished.
	 *
	 * @return bool Whether the Rewrite & Republish copy can be republished.
	 */
	isCopyAllowedToBeRepublished() {
		const currentPostStatus = select( 'core/editor' ).getCurrentPostAttribute( 'status' );

		if ( currentPostStatus === 'dp-rewrite-republish' || currentPostStatus === 'private' ) {
			return true;
		}

		return false;
	}

	/**
	 * Renders the notices in the block editor.
	 *
	 * @returns {void}
	 */
	renderNotices() {
		if ( ! duplicatePostNotices || ! ( duplicatePostNotices instanceof Object ) ) {
			return;
		}

		for ( const [ key, notice ] of Object.entries( duplicatePostNotices ) ) {
			if ( notice.status && notice.text ) {
				dispatch( 'core/notices' ).createNotice(
					notice.status,
					notice.text,
					{
						isDismissible: notice.isDismissible || true,
					}
				);
			}
		}
	}

	/**
	 * Removes the slug panel from the block editor sidebar when the post is a Rewrite & Republish copy.
	 *
	 * @returns {void}
	 */
	removeSlugSidebarPanel() {
		if ( parseInt( duplicatePost.rewriting, 10 ) ) {
			dispatch( 'core/edit-post' ).removeEditorPanel( 'post-link' );
		}
	}

	/**
	 * Renders the links in the PluginPostStatusInfo component.
	 *
	 * @returns {JSX.Element} The rendered links.
	 */
	render() {
		// Don't try to render anything if there is no store.
		if ( ! select( 'core/editor' ) || ! ( wp.editPost && wp.editPost.PluginPostStatusInfo ) ) {
			return null;
		}

		const currentPostStatus = select( 'core/editor' ).getEditedPostAttribute( 'status' );

		const [ willBeDeletedReference, setWillBeDeletedReference ] = useState( false );

		return (
			( duplicatePost.showLinksIn.submitbox === '1' ) &&
			<Fragment>
				{ ( duplicatePost.newDraftLink !== '' && duplicatePost.showLinks.new_draft === '1' ) &&
					<PluginPostStatusInfo>
						<Button
							isTertiary={ true }
							className="dp-editor-post-copy-to-draft"
							href={ duplicatePost.newDraftLink }
						>
							{ __( 'Copy to a new draft', 'duplicate-post' ) }
						</Button>
					</PluginPostStatusInfo>
				}
				{ ( currentPostStatus === 'publish' && duplicatePost.rewriteAndRepublishLink !== '' && duplicatePost.showLinks.rewrite_republish === '1' ) &&
					<PluginPostStatusInfo>
						<Button
							isTertiary={ true }
							className="dp-editor-post-rewrite-republish"
							href={ duplicatePost.rewriteAndRepublishLink }
						>
							{ __( 'Rewrite & Republish', 'duplicate-post' ) }
						</Button>
					</PluginPostStatusInfo>
				}
				{ ( duplicatePost.originalPostTitle !== '' && duplicatePost.showOriginal === '1' ) &&
					<PluginDocumentSettingPanel
						name="duplicate-post-panel"
						title={ __( "Duplicate Post", "duplicate-post" ) }
						className="custom-panel"
					>
						<p>
							{
								sprintf(
								/* translators: %s: post title. */
									__(
										'The original item this was copied from is: %s',
										'duplicate-post'
									),
									duplicatePost.originalPostTitle
								)
							}
						</p>
						<ToggleControl
							label={ __( "Delete reference to original item.", "duplicate-post" ) }
							help={
								willBeDeletedReference
									? __( "The reference will be deleted on update", "duplicate-post" )
									: __( "The reference will be kept on update", "duplicate-post" )
							}
							checked={ willBeDeletedReference }
							onChange={ (newValue) => {
								setWillBeDeletedReference( newValue );
							} }
						/>
					</PluginDocumentSettingPanel>
				}
			</Fragment>
		);
	}
}

const instance = new DuplicatePost();
instance.handleRedirect();

registerPlugin( 'duplicate-post', {
	render: instance.render
} );
