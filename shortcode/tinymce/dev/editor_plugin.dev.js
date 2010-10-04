(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('mueventsshortcode');

	tinymce.create('tinymce.plugins.MUEventsShortcode', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) 
		{
			var t = this;
			this.plugin_url = url ;
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
			ed.addCommand('muEventInsert', function( params )
			{
				if( params == false )
					params = "" ;
				else
					params = '?params=' + params
					
				ed.windowManager.open({
					file : url + '/muEvent_editorDialog.php' + params ,
					width : 340 + ed.getLang('mueventsshortcode.delta_width', 0),
					height : 340 + ed.getLang('mueventsshortcode.delta_height', 0),
					inline : 1,
					popup_css : false
				}, {
					plugin_url : url // Plugin absolute URL
				});
			});
			
			// Register example button
			ed.addButton(
				'mueventsshortcode', 
				{
					title : ed.getLang('mueventsshortcode.desc'),
					'class' : 'mueventsshortcode',
					cmd : 'muEventInsert',
					image : url + '../../../images/mu_events_mce_icon_up.png'
				}
			);
			ed.onDblClick.add(function(ed, e) 
			{
				if ( e.target.nodeName == 'IMG' && ed.dom.hasClass(e.target, 'muEventMCE') )
				{
					tinyMCE.activeEditor.execCommand('muEventInsert', tinymce.trim(e.target.title)) ;
				}
			});
			ed.onBeforeSetContent.add(function(ed, o) 
			{
				o.content = t._mod_content( o.content, url ) ;
			});

			ed.onPostProcess.add(function(ed, o) {
				if (o.get)
					o.content = t._get_eventlisting(o.content);
			});
		},
		_mod_content : function( co , url) {
			return co.replace( 
				/\[mu-events([^\]]*)\]/g , 
				function( a , b )
				{
					return '<img src="'+url+'../../../images/mu_events_mce_placeholder_icon.png" class="muEventMCE mceItem" style="cursor:pointer;cursor:hand;" title="mu-events' + tinymce.DOM.encode( b ) + '" />';
				}
			);
		},
		_get_eventlisting : function(co) {

			function getAttr(s, n) {
				n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
				return n ? tinymce.DOM.decode(n[1]) : '';
			};

			return co.replace(/(?:<p[^>]*>)*(<img[^>]+>)(?:<\/p>)*/g, function(a,im) {
				var cls = getAttr(im, 'class') ;

				if ( cls.indexOf('muEventMCE') != -1 )
					return '<p>['+tinymce.trim(getAttr(im, 'title'))+']</p>';

				return a;
			});
		},
		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'accessible_media',
				author : 'aut0poietic',
				authorurl : 'http://aut0poietic.us',
				infourl : 'http://aut0poietic.us',
				version : "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('mueventsshortcode', tinymce.plugins.MUEventsShortcode);
})();