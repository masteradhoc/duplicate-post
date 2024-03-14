import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

const icon = <svg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 20 20'>
	<path
		d='M18.9 4.3c0.6 0 1.1 0.5 1.1 1.1v13.6c0 0.6-0.5 1.1-1.1 1.1h-10.7c-0.6 0-1.1-0.5-1.1-1.1v-3.2h-6.1c-0.6 0-1.1-0.5-1.1-1.1v-7.5c0-0.6 0.3-1.4 0.8-1.8l4.6-4.6c0.4-0.4 1.2-0.8 1.8-0.8h4.6c0.6 0 1.1 0.5 1.1 1.1v3.7c0.4-0.3 1-0.4 1.4-0.4h4.6zM12.9 6.7l-3.3 3.3h3.3v-3.3zM5.7 2.4l-3.3 3.3h3.3v-3.3zM7.9 9.6l3.5-3.5v-4.6h-4.3v4.6c0 0.6-0.5 1.1-1.1 1.1h-4.6v7.1h5.7v-2.9c0-0.6 0.3-1.4 0.8-1.8zM18.6 18.6v-12.9h-4.3v4.6c0 0.6-0.5 1.1-1.1 1.1h-4.6v7.1h10z'
		fill='rgba(240,245,250,.6)'/>
</svg>

registerPlugin(
	'duplicate-post-tools',
	{
		render: () => {
			wp.commands.useCommand(
				{
					name: "duplicate-post/new-draft",
					label: __( "Copy to a new draft", "duplicate-post" ),
					icon: icon,
					callback: ({close}) => {
						document.location = duplicatePost.newDraftLink
					},
				}
			);
			wp.commands.useCommand(
				{
					name: "duplicate-post/rewrite-republish",
					label: __( "Rewrite & Republish", "duplicate-post" ),
					icon: icon,
					callback: ({close}) => {
						document.location = duplicatePost.rewriteAndRepublishLink
					},
				}
			);
		}
	}
);
