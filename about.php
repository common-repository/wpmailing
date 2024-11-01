<?php

/**
 * WPMailing plugin for WordPress 2.x
 * ----------------------------------
 * This file is part of WPMailing 1.0
 * ----------------------------------
 * Author: Cau Guanabara
 * Website: http://cauguanabara.jsbrasil.com
 * ----------------------------------
 */

global $mailing_version, $mailing_stability_status, $mailing_uptodate, $mailing_base_url;
?>
<div class="wrap">
	<h2><?php _e('WPMailing plugin for WordPress', 'mailing'); ?></h2>
	<blockquote>
	</blockquote>
	<div id="abcont">
	  <div id="hmenu">
		<a href="javascript://" class="sel" onclick="openPanel('wpm_about')"><?php 
		  _e('WPMailing', 'mailing'); ?></a>
		<a href="javascript://" onclick="openPanel('wpm_help')"><?php 
		  _e('Help', 'mailing'); ?></a>
		<a href="javascript://" onclick="openPanel('wpm_faq')"><?php 
		  _e('FAQ', 'mailing'); ?></a>
		<a href="javascript://" onclick="openPanel('wpm_donate')"><?php 
		  _e('Donate', 'mailing'); ?></a>
		</div>
		<div id="visiblecont">
		  <div id="wpm_about">	
			  <h4><?php _e('About WPMailing', 'mailing'); ?></h4>
	      <blockquote><?php _e('Basically, WPMailing gives you the ability to organize the users of your blog in groups and offers an interface to send them e-mails based in these groups. Some little modifications are also made in WordPress administrative interface - without modifying any original file.', 'mailing'); ?></blockquote>

			  <h4><?php _e('The modifications made by WPMailing', 'mailing'); ?></h4>
	      <blockquote><?php _e('Once installed, WPMailing adds some new features to the WP administrative interface, by adding new pages or through Javascript. See below.', 'mailing'); ?>
					<p class="bld"><?php _e('Pages added to WordPress', 'mailing'); ?>:</p>
				<ul>
				  <li>
					<?php _e('Under Write menu, adds a page to compose and send e-mails', 'mailing'); ?>
					</li>
				  <li>
					<?php _e('Under Manage menu, adds a page to edit your user groups', 'mailing'); ?>
					</li>
				  <li>
					<?php _e('Under Plugins menu, adds this page to clarify you about WPMailing', 'mailing'); ?>
					</li>
				  <li>
					<?php _e('Under Options menu, adds a page to edit preferences and settings', 'mailing'); ?>
					</li>
				</ul>
					<p class="bld"><?php _e('Modifications made by Javascript in existent pages', 'mailing'); ?>:</p>
				<ul>
				  <li>
					<?php _e('On dashboard, adds direct links to WPMailing pages', 'mailing'); ?>
					</li>
				  <li>
					<?php _e('Modifies all pages in the users section, by adding some items to give support for groups management', 'mailing'); ?> 
					(users.php, user-edit.php and profile.php)
					</li>
				  <li>
					<?php _e('Applies WPM stamp to the upper right corner of every page that was added or modified by WPMailing', 'mailing'); ?>
					</li>
				</ul>
				</blockquote>

			  <h4><?php _e('Browsers compatibility', 'mailing'); ?></h4>
	      <blockquote>
				<?php _e('When loaded, WPMailing adds more then 400 lines of javascript code to the WordPress administrative pages, therefore this is an important item. The system was successfully tested in the following navigators', 'mailing'); ?>:
				<ul>
				<li>Firefox 2</li>
				<li>Internet Explorer 7</li>
				<li>Internet Explorer 6</li>
				<li>Mozilla 1.7</li>
				<li>Netscale 7.2</li>
				<li>Opera 9</li>
				</ul>
				</blockquote>

			  <h4><?php _e('Credits', 'mailing'); ?></h4>
	      <blockquote>
				<?php _e('Author', 'mailing'); ?>: 
				  <a href="mailto:caugb@jsbrasil.com/">Cau Guanabara</a> <br />
				<?php _e('Site', 'mailing'); ?>: 
				  <a href="http://cauguanabara.jsbrasil.com/">http://cauguanabara.jsbrasil.com/</a> <br />
				<?php _e('Current version', 'mailing'); ?>: <?php print $mailing_version; ?> <br />
				<?php _e('Current status', 'mailing'); ?>: <?php print $mailing_stability_status; ?> <br />
				<?php _e('Start date', 'mailing'); ?>: 2007-07-07 <br />
				<?php _e('Last update', 'mailing'); ?>: <?php print $mailing_uptodate; ?> <br />
				</blockquote>
      </div>

		  <div id="wpm_help" style="display:none;">
			  <h4><?php _e('WPMailing help', 'mailing'); ?></h4>
	      <blockquote>
				<h5><?php _e('Creating groups and applying it to your users', 'mailing'); ?></h5>
				<p><?php _e('There is no interface to create a new group, but you can do that when adding a new user or later, by editing the user settings. Just go to the habitual users administration interface and use the new features added by WPMailing. To create a new group, just add the name of the group to the groups field (always separated by commas) and submit. After create a group, you can define a description to this group, to help your organization. Do it in: Manage > Groups.', 'mailing'); ?></p>
				<p class="hlpinfo"><?php _e('What we call group, actually, is a tag and not a container. Each user can belong to various groups without duplication of the records in database or when when sending e-mails.', 'mailing'); ?></p>

				<h5><?php _e('Sending e-mails to the users', 'mailing'); ?></h5>
				<ul>
				<li>
				<p class="bld" style="font-style:italic">
				<?php _e('The syntax to the recipients in field To', 'mailing'); ?></p>
				<p><?php _e('This field has an particular syntax that allows you to send e-mails to the blog users based on their groups, capabilities or user levels. Use one of the following keywords: group, role, level or user, with this formula', 'mailing'); ?>:
				&quot;&lt;&quot; + <?php _e('keyword', 'mailing'); ?> + &quot;:&quot; + <?php _e('value', 'mailing'); ?> + &quot;&gt;&quot; <?php _e('where value is corresponding value. For example, if you want to use the group mygroup, keyword is &quot;group&quot; and value is &quot;mygroup&quot;.', 'mailing'); ?></p>
				<p class="bld"><?php _e('Some valid examples', 'mailing'); ?>:</p>
					<ul>
					<li>&lt;group:mygroup&gt; : 
							<?php _e('includes all users tagged with mygroup', 'mailing'); ?></li>
					<li>&lt;role:author&gt; : 
							<?php _e('includes all users with author capability', 'mailing'); ?></li>
					<li>&lt;level:5&gt; : 
							<?php _e('includes all users with level 5', 'mailing'); ?></li>
					<li>&lt;user:john&gt; : 
							<?php _e('includes the user john', 'mailing'); ?></li>
					<li>&quot;John Smith&quot; &lt;john@server.com&gt; : 
							<?php _e('includes an e-mail to John Smith', 'mailing'); ?></li>
					<li>John Smith &lt;john@server.com&gt; : 
							<?php _e('includes an e-mail to John Smith', 'mailing'); ?></li>
					<li>John Smith john@server.com : 
							<?php _e('includes an e-mail to John Smith', 'mailing'); ?></li>
					<li>john@server.com : 
							<?php _e('includes an e-mail to john', 'mailing'); ?></li>
					</ul>
				<p class="hlpinfo"><?php _e('You can include two or more groups that contain the same user. The recipients are checked by e-mail address and after the first message sent to a particular e-mail, this address will be automatically rejected by the system.', 'mailing'); ?></p>
				</li>
				
				<li>
				<p class="bld" style="font-style:italic">
				<?php _e('Special values that you can use in your message', 'mailing'); ?></p>
				<p><?php _e('There are some special values that you can use in the message body. These values will be replaced with some informations about the user who will receive the message and with values of the selected blog content (post or page).', 'mailing'); ?></p>
				<p class="bld"><?php _e('Current user values to personalize the message', 'mailing'); ?></p>
					<ul>
					<li>%uid : <?php _e('The ID of the current user', 'mailing'); ?></li>
					<li>%name : <?php _e('The name (or login, if name is empty) of the current user', 'mailing'); ?></li>
					<li>%username : <?php _e('The login name of the current user', 'mailing'); ?></li>
					<li>%email : <?php _e('The e-mail of the current user', 'mailing'); ?></li>
					<li>%website : <?php _e('The website of the current user', 'mailing'); ?></li>
					<li>%groups : <?php _e('The groups of the current user (separated by commas)', 'mailing'); ?></li>
					<li>%level : <?php _e('The level of the current user', 'mailing'); ?></li>
					<li>%capabilities : <?php _e('The capabilities of the current user (separated by commas)', 'mailing'); ?></li>
					</ul>
				<p class="hlpinfo"><?php _e('The values above will be available only to recipients added by group, role, level or username (user), with our formula <xxx:vvv>. If you include recipients directlly by e-mails, like in the four last examples of the topic above (addressed to john), only %email and %name will be available and %name will be filled with the user portion of the e-mail address.', 'mailing'); ?></p>
				<p class="bld"><?php _e('Values of the selected post or page', 'mailing'); ?></p>
					<ul>
					<li>%ID : <?php _e('The ID of the selected publication', 'mailing'); ?></li>
					<li>%post_date : <?php _e('Date and time of the publication', 'mailing'); ?></li>
					<li>%post_title : <?php _e('Title of the publication', 'mailing'); ?></li>
					<li>%post_content : <?php _e('Publication content', 'mailing'); ?></li>
					<li>%post_category : <?php _e('The category', 'mailing'); ?></li>
					<li>%post_author : <?php _e('The ID of the author', 'mailing'); ?></li>
					<li>%author_name : <?php _e('The name of the author', 'mailing'); ?></li>
					<li>%post_type : <?php _e('Publication type (post or page)', 'mailing'); ?></li>
					</ul>
				<p class="hlpinfo"><?php _e('There are some other available values related to the selected publication. Actually you can use all fields returned by get_post() WordPress function.', 'mailing'); ?> 
				<a href="http://codex.wordpress.org/Function_Reference/get_post#Return" target="_blank" title="<?php _e('See all possible return values', 'mailing'); ?>">http://codex.wordpress.org/Function_Reference/get_post</a></p>
				</li>
				</ul>

				</blockquote>
			</div>
		  <div id="wpm_faq" style="display:none;">
			  <h4><?php _e('Frequently asked questions', 'mailing'); ?></h4>
	      <blockquote>
				<p><?php //_ e('There is ...', 'mailing'); ?></p>
				<ul>
				<li><p class="bld"><?php _e('Does WPMailing modify any WordPress file?', 'mailing'); ?></p>
				<p><?php _e('No. WPMailing modifies some WordPress pages with Javascript, after that pages are loaded and the hard-code is not affected.', 'mailing'); ?></p>
				</li>
				<li><p class="bld"><?php _e('Where does WPMailing store the users information?', 'mailing'); ?></p>
				<p><?php _e('WPMailing uses the default WordPress users system and do not create any new table in your database. But some extra information are stored via set_option() function.', 'mailing'); ?></p>
				</li>
				<li><p class="bld"><?php _e('The structure of my WP installation will be affected after I have deactivated WPMailing?', 'mailing'); ?></p>
				<p><?php _e('The structure will be not affected, but you can decide to preserve some information and other configuration values for a future installation of WPM or remove all data when deactivating WPM. Before deactivating the plugin, go to Options > E-mail and set your preference.', 'mailing'); ?></p>
				</li>
				<li><p class="bld"><?php _e('How to insert new contacts to my mailing list?', 'mailing'); ?></p>
				<p><?php _e('Just add new users to your WordPress blog, tagging them with appropriate groups.', 'mailing'); ?></p>
				</li>
				</ul>

	      </blockquote>
			</div>
		  <div id="wpm_donate" style="display:none;">
			  <h4><?php _e('Contribute with WPMailing development', 'mailing'); ?></h4>
			<blockquote><p><?php _e('If you like this free software, you can contribute with WPMailing in several ways', 'mailing'); ?></p>
				<ul>
				<li>
				<p class="bld"><?php _e('Give me a feedback', 'mailing'); ?></p>
				<p><?php _e('I would like very much to receive your opinion, suggestion or critical. This the very first release of WPM and I am not completely secure than everything is alright. Thats because your feedback is important to me, therefore please, feel free to write me.', 'mailing'); ?></p>
				</li>
				<li>
				<p class="bld"><?php _e('Help me to find and repair some possible problems', 'mailing'); ?></p>
				<p><?php _e('If you have found a bug or some minor problem in this script, contact me and let me know about the problem and what you were trying to do when it happened.', 'mailing'); ?></p>
				</li>
				<li>
				<p class="bld"><?php _e('Correct my bad English', 'mailing'); ?></p>
				<p><?php _e('If are you reading it in english, you need to know: this text is the result of two different translators and my poor english translating what I have thought in my own language - I read only technical texts, never speak or write in english, so there must be several errors... Send me your corrections, I will apply it.', 'mailing'); ?></p>
				</li>
				<li>
				<p class="bld"><?php _e('Help me on development', 'mailing'); ?></p>
				<p><?php _e('If you are a web developer and have found an error, a better way to do something or have thought about something that I did not, I will appreciate your help.', 'mailing'); ?></p>
				</li>
				<li>
				<p class="bld"><?php _e('Make a little donation online', 'mailing'); ?></p>
				<p><?php _e('I am a self-taught programmer who fights to stay alive in Brazil as a free lance web developer. If you think WPM deserves, I will accept a little contribution with happiness. Thank you for the recognition.', 'mailing'); ?></p>
				<p>
				<a href="https://www.paypal.com/cgi-bin/webscr" style="text-decoration:none" onclick="
				document.ppform.submit(); return false;"><img src="<?php print $mailing_base_url; 
				?>images/paypal.gif" border="0" alt="PayPal" title="Make donations with PayPal - it's fast, free and secure!" /></a>
				&nbsp;
				<a href="http://4659403.e-gold.com/" style="text-decoration:none"><img src="<?php 
				  print $mailing_base_url; ?>images/egold.gif" border="0" alt="E-gold" title="Donate with E-gold" /></a>
				</p>
				</li>
				</ul></blockquote>
      </div>
		</div>
	</div>
</div>
<form name="ppform" action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
</form>
