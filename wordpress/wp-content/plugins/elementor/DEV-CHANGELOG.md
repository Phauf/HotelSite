# Elementor Developer Edition - by Elementor.com

#### 3.5.0-dev36 - 2021-10-08
* Fix: Admin Top Bar experiment causes the WP "Add New Plugin" to disappear [ED-5090] (#16504)
* New: Drag image from desktop [ED-3817] (#15652)
* Fix: Section handler not reachable if Scroll snap is active [ED-4926] (#16508)
* Tweak: Updating the dialog library. (#16164)
* Tweak: Added reverse columns to Additional Custom Breakpoints Experiment (ED-4631) (#16098)

#### 3.5.0-dev35 - 2021-10-07
* Tweak: Height and width fields in Responsive bar cannot be edit in Desktop [ED-4529] (#16198)
* Fix: When adding margin to column of media-carousel widget it breaks the UI [ED-4915] (#16258)
* Tweak: Added an option for Storke in multiple widgets [ED-3914] (#16029)
* Tweak: Adjusting the widget promotional popup CTA text [ED-3971] (#16438)
* Tweak: New Experiments UI [ED-4179] (#16233)
* Tweak: Deprecate old properties [ED-4880] (#16158)
* Fix: Dividers not vertically centered in Icon List wisget [ED-5053] (#16440)

#### 3.5.0-dev34 - 2021-10-06
* Fix: Slides per view controls disappeared in multiple breakpoints in Testimonial Carousel [ED-5025] (#16388)

#### 3.5.0-dev33 - 2021-10-03
* Fix: Default value check for control validity [ED-5071] (#16437)

#### 3.5.0-dev31 - 2021-10-01
* Fix: When the "Optimized DOM" experiment is off and custom breakpoints are defined... [ED-4994] (#16385)

#### 3.5.0-dev30 - 2021-09-30
* Tweak: Apply filter for activate user in admin tool bar [ED-4309] (#15714)
* Fix: GitHub - Update servers to ubuntu 20.04 (#16413)
* Fix: GitHub - Update servers to ubuntu 20.04 (#16416)
* Fix: Admin top bar breaks admin dashboard [ED-5044] (#16424)

#### 3.5.0-dev28 - 2021-09-19
* Tweak: Adding a URL parameter to the import-export that skips the kit-content selection [ED-4750] (#16275)
* Tweak: Internal - Allow external extending of Element Editing Handles Buttons [ED-4480] (#16232)

#### 3.5.0-dev26 - 2021-09-15
* Tweak: Updated featured video in readme.txt (#16332)

#### 3.5.0-dev25 - 2021-09-14
* Fix: Elements panel UI glitch [ED-4959] (#16282)
* Fix: Offsets not working properly with an absolutely positioned widget and Improved DOM experiment [ED-4945] (#16253)

#### 3.5.0-dev24 - 2021-09-13
* Fix: If a responsive control is the first control in a popover, it breaks the popover for non-desktop devices   [ED-4979] (#16318)
* Fix: Large images are fully displayed before the swiper is initialized. (#16317)
* Tweak: Updated changelog for v3.4.4 (#16326)

#### 3.5.0-dev23 - 2021-09-13
* Fix: Element dragging after ctrl + click on Mac [ED-1827] (#15980)

#### 3.5.0-dev21 - 2021-09-10
* Tweak: CSS Transform - Change flip icon [ED-4462] (#16284)

#### 3.5.0-dev19 - 2021-09-06
* Fix: Editor doesn't load on v3.5.0 if `ELEMENTOR_DEBUG` is enabled [ED-4937] (#16256)

#### 3.5.0-dev18 - 2021-09-05
* Fix: Default global values override local global values [ED-4917] (#16206)

#### 3.5.0-dev16 - 2021-09-03
* JS API/Editor: Fix - Move 'Editor/Documents' to components folder. (#14602)
* Fix: Image content html tags appeared on Image carousel widget [ED-4828] (#16129)
* Tweak: Controls PHPCS (#16141)
* Tweak: Added "Justified" text alignment to columns & sections (#11512)
* Tweak: Add new "Word Spacing" control to typography controls [ED-4621] (#9152)
* Fix: PHP Lint failing during syntax lint (#16165)
* Tweak: Add step size to typography "Word Spacing" control [ED-4621] (#16167)
* Fix: Unable to import a kit via URL when not logged-in to WP [ED-4836] (#16157)
* Tweak: Show admin-top-bar only on elementor pages [ED-4879, ED-4899] (#16190)
* Tweak: Updated changelog for v3.4.3 (#16194)
* Tweak: Updated changelog for v3.4.3 (#16197)
* Fix: Page settings layout description [ED-1210] (#13360)
* Tweak: Add perspective to CSS transform [ED-4304] (#15774)
* Tweak: Changed default values experiment name [ED-4876] (#16155)
* Fix: Lighthouse CI test is failing - removed unused css [ED-3698] (#16200)
* Tweak: CSS Transform - Change flip icon [ED-4462] (#15933)
* Fix: Optimize Kit library index page performance [ED-4669] (#16201)
* Revert "Fix: Lighthouse CI test is failing - removed unused css [ED-3698] (#16200)" (#16207)
* Fix: Data Updater causes fatal error due to DB corruption [ED-4839] (#16195)
* Fix: SVG sanitizer is failing if there is a line break after "</svg" [ED-4853] (#16132)
* Tweak: Responsive bar - Make the minimum height smaller for all responsive devices (ED-4359) (#16017)
* Tweak: Internal - Add external filter for Context Menu Groups [ED-4483] (#16160)
* Tweak: Add CSS Transform section [ED-4767] (#16064)

#### 3.5.0-dev14 - 2021-08-26
* Fix: consistent hook names (#16099)
* New: Default values first iteration [ED-3683] (#15518)
* Fix: Landing page builder experiments causes page not found/ 404 errors with media files URLs (#15943) (ED-4806) (#16096)
* Fix: Animated elements disappear before entering the viewport (#2806) (ED-2513) (#16095)
* Tweak: Added Elementor price plan filter to Kit Library (closes #16075) [ED-4804] (#16102)
* Tweak: Entrance Animations - Once the element has been animated, unobserve it [ED-4845] (#16127)
* Tweak: Internal - Remove all usages of `Elementor\Utils::get_create_new_post_url()` (#16128)
* Fix: Controls in the Editor's JS system always have an empty string value as default, so they cannot be deleted (ED-4772) (#16042)
* Fix: Can't upload SVG files using Elementor (#16084, #16119, #16088) [ED-4813] (#16125)
* Fix: `wp_kses_post` strips `srcset` attribute from images (#16111) [ED-4840] (#16122)
* Fix: Inline CSS is parsed to an invalid charcaters. (#16143)
* Fix: When the inline-SVG experiment is active the list icons alignment can not be changed [ED-4758] (#16109)
* Fix: Autoplay not working for Vimeo videos in Lightbox (ED-4796) (#16068)
* Fix: Missing translations escaping in default values module (#16151)

#### 3.5.0-dev13 - 2021-08-23
* New: Docs - UI States [ED-4628] (#15961)
* Fix: Reflect inherited value in slider control [ED-4766] (#16040)
* Tweak: Library - On open, don't query all templates [ED-3149] (#15662)
* Fix: Custom Code Promotion [ED-508] (#15960)
* Tweak: Changed default cards view in Kit Library [ED-4484] (#15982)
* Tweak: Added the option to search by tag names in Kit Library [ED-4482] (#15959)
* Tweak: Changed Kit Library tab name [ED-3727] (#15986)
* Fix: Finder incorrectly identifies pages created. [ED-3708] (#15352)
* Fix: Admin Top Bar conflicts with WP customizer [ED-4768] (#16101)

#### 3.5.0-dev11 - 2021-08-19
* Revert: Fix: Gradient control doesn't work on frontend when using Global Colors (#16053)
* Fix: Background image controls missing when using dynamic image (Closes #16050) [ED-4785] (#16062)
* Fix: Motion effects popover is not visible since v3.4.1 (#16044) [ED-4788] (#16061)
* Fix: Responsive Site settings are not being applied on frontend when Additional Custom Breakpoints is active (ED-4787) (#16060)
* Tweak: Updated Changelog to v3.4.2 (#16066)

#### 3.5.0-dev10 - 2021-08-18
* Fix: Internal - `{device}_default` control properties are not deleted for responsive controls (ED-4741) (#16004)
* Fix: Gradient control doesn't work on frontend when using Global Colors (ED-3517) (#16002)
* Tweak: Added source=generic parameter when connecting through the top bar [ED-4459] (#15998)
* Fix: Control conditions are not being executed when has dash or underscore in the control slug (ED-4747) (#16014)
* Tweak - Adding SVG support to the global video play-icon. (#16031)
* Fix: Placeholder values of column width shouldn't cascade to mobile [ED-4664] (#16038)
* Tweak: Updated changelog for v3.4.1 (#16039)
* Fix: Source param at get_client_id request [ED-4459] (#16041)

#### 3.5.0-dev9 - 2021-08-17
* Tweak: WP_Query - Don't calc the total rows if not needed [ED-3150] (#15688)
* Tweak: Custom Code - Added promotion [ED-508] (#15615)
* Fix: Multiple repeaters in same widget has conflicts [ED-4001] (#15612)
* Fix: App - "x" button after refresh sent to the theme builder page [ED-4042] (#15616)
* Tweak: Page Transitions - Change icon [ED-4318] (#15722)
* Tweak: updated changelog for v3.4 (#15984)
* Fix: Basic Gallery - SVG Icon is not displayed in the Basic Gallery widget [ED-4046] (#15551)
* Tweak: Preparation in Core for future Page Transitions in Pro [ED-4571] (#15992)
* Fix: Editor - Some respon. controls pass the desktop default to other devices accidentally (ED-4670) (#15962)
* Fix: Alignment control in Testimonial widget doesn't work in responsive view (ED-4531) (#15995)
* Fix: Button alignment functionality not responding with Additional Custom Breakpoints (ED-4675) (#15981)
* Fix: Price & Quantity Ampersand sign is in HTML in Menu Cart widget (ED-4694) (#15997)
* Tweak: Kit Library - Missing post types in Site Parts overview screen [ED-4485] (#15988)
* Fix: Placeholder values of column width shouldn't cascade to mobile [ED-4664] (#15966)
* Fix: Activation bug for IDN domains [ED-886] (#15983)

#### 3.5.0-dev7 - 2021-08-13
* Fix: Responsive values cascade wrongly with different dimensions on different breakpoints [ED-4665] (#15955)

#### 3.5.0-dev6 - 2021-08-12
* New: Add dev-changelog.md on developer-edition release [ED-4286] (#15893)
* Fix: Widescreen breakpoint preview didn't fit the screen [ED-4504] (#15941)
* Fix: The position of the SVG icon is different from the i tag icon position. (#15937)
* Fix: Column width value cascading bottom limit was tablet instead of mobile-extra (ED-4661) (#15949)
* Tweak: Make the section element extendable (#13618)

#### 3.5.0-dev5 - 2021-08-11
* Fix: Gallery widget with dynamic tags can't be edited [ED-4443] (#15912)

#### 3.5.0-dev4 - 2021-08-11
* Revert "Tweak: Preparation in Core for future Page Transitions in Pro [ED-4571] (#15891)" (#15911)
* Tweak: Export `utils/escapeHTML()` to `elementorFrontend.utils` [ED-4308] (#15712)
* Fix: "Auto" text not showing in section margin control [ED-4534] (#15880)
* Fix: Theme builder not working when import export feature is inactive [ED-4563] (#15884)
* Fix: Re-migrate Kit - The migration fails in some cases [ED-4457] (#15478)
* Tweak: Blend Mode - Missing translations in section and column (#15900)
* Tweak: Additional Breakpoints - Added new _NEXT SCSS vars for mobile & tablet extra (ED-4596) (#15915)
* Fix: Breakpoints - Desktop max point when widescreen is inactive is 1439px instead of 99999px (ED-4595) (#15926)
* Tweak: Missing translations (#15923)
* Fix: UI glitch in Responsive bar in RTL sites [ED-4558] (#15924)
* Fix: Responsive columns didn't reacted to the appropriate breakpoints (ED-4556) (#15927)
* Fix: All styles under frontend/general were duplicated (#15925)
* Tweak: Additional Custom Breakpoints Experiment changed to Beta phase (ED-4600) (#15928)

#### 3.5.0-dev3 - 2021-08-06
* Fix: When additional breakpoints are active - responsive switcher is slow [ED-4411] (#15849)
* Tweak: Updated getting started video course [ED-4570] (#15894)
* Tweak: Preparation in Core for future Page Transitions in Pro [ED-4571] (#15891)

#### 3.5.0-dev2 - 2021-08-04
* Fix - When the inline-font-svg experiment is active the font-awesome library is not loaded by default inside the editor when not using a dedicated icons control. (#15855)
* Fix: Additional Breakpoints - Responsive 'base-multiple' controls with a default value inherit the desktop default as an actual value and not passively (#15860)
* Fix: Breakpoints/multiple default values (#15864)
* Fix: Select 2 - UI glitch in Dark mode [ED-4458] (#15818)
* Fix: Undefined "pro_widgets" array key in certain cases [ED-4528] (#15876)
* Fix: Image placeholder only cascades one level down [ED-4506] (#15863)
* Fix: Placeholder units not always reflected in dimension controls [ED-4505] (#15867)
* Fix: Placeholder values on desktop don't cascade up from Desktop to Widescreen [ED-4509] (#15868)
* Tweak: Change publish (#15879)

#### 3.5.0-dev1 - 2021-08-03
* Kit-Library: New - Init (#14184)
* Fix: Kit-Library - Return access_level from connect process. (#14714)
*  New: Kit-Library - Connect to download link. [ED-2712] (#14652)
* New: Kit-Library - Favorites [ED-2598] (#14650)
* Fix: Kit-Library - Taxonomies new API scheme [ED-3143] (#14864)
* Tweak: Kit-Library - Sorting [ED-2597] (#14737)
* Tweak: Kit-Library - Clicking a page in the kit overview opens live preview [ED-3134] (#15006)
* Tweak: Kit-Library - Text Fixes [ED-3130] (#15024)
* Fix: Kit-library - Re add react-query (#15030)
* Tweak: Kit-Library - sort by featured [ED-3251] (#15057)
* Tweak: Kit-Library - Add additional connect info to the api [ED-3360] (#15070)
* Fix: Kit-Library - Sort authentication headers a-z [ED-3380] (#15084)
* Fix: Kit-Library - Rejects from spec [ED-3357] (#15081)
* Tweak: Kit-Library - Send nonce and referrer to the import process [ED-3252] (#15110)
* Tweak: Kit-library - Made the filter tags clickable [ED-2904] (#14923)
* Tweak: Kit-Library - favorite kits empty state screen [ED-3131] (#14980)
* Tweak: Kit-Library - not loaded if import-export experiment is off [ED-3335] (#15058)
* Fix: Kit-Library - Move components from app to kit-library [ED-3410] (#15115)
* Fix: Kit-Library - Pages section not exists [ED-3416] (#15145)
* Tweak: Kit-Library - production url [ED-3440] (#15147)
* Tweak: Kit-Library - Styling dark mode [ED-3479] (#15179)
* Fix: Kit-Library - Using function that not exists in WP 5.6 [ED-3564] (#15235)
* Fix: Kit-Library - Separate kit access level from template access level [ED-3565] (#15238)
* New: Kit-Library - Tests [ED-3135] (#15113)
* Fix: Kit-Library - Taxonomies disappear [ED-3574] (#15246)
* Fix: Kit-Library - import not working [ED-3436] (#15280)
* Fix: Kit-Library - Page doc type [ED-3572] (#15239)
* Tweak: Kit-Library - Add Spinner to apply button [ED-3611] (#15299)
* Tweak: Kit-Library - Changed back button behavior [ED-3618] (#15318)
* Tweak: Kit-Library - Added Envato promotion [ED-3608] (#15326)
* Tweak: Improved PHP Lint enforcement [ED-2901] (#15334)
* Fix: PHP Lint - Security Linter [ED-3168, ED-2901] (#14948)
* Fix: PHP Lint [ED-3813] (#15422)
* Tweak: Controls System Optimization (ED-3378, ED-3643, ED-3744, ED-3814, ED-3839, ED-3855) (#15247)
* Tweak: Additional Breakpoints - Fixes, Widgets and Config adjustments (ED-3894, ED-3935, ED-3934) (#15446)
* Tweak: Fix merge conflicts between Additional Breakpoints and Dev Edition (#15580)
* Tweak: Font-Awesome icons as inline SVG [ED-2081] (#15236)
* Tweak: Additional Breakpoints - System tweaks (ED-3907, ED-3852, ED-3999, ED-3943, ED-4123) (#15465)
* Fix: The widgets inline CSS was printed as plain text. (#15666)
* Responsive bar: Fix - Widescreen breakpoint reject values entered in the Responsive bar [ED-4216] (#15689)

#### 3.4.0-dev13 - 2021-07-22
* Testimonial Alignment: Tweak - Responsive Added to Alignment control in Testimonial widget [ED-2812] (#15158)
* Fix: Exclude elementor templates from WP default sitemap closes [ED-583] (#15363)
* Fix: WP audio widget only shows correct styling in live preview [ED-855] (#15380)
* Fix: New tabs do not appear in Tabs Widget if alignment is not Default or Start [ED-1651] (#14686)
* Tweak: Usage - Add additional data [ED-798] (#13406)
* Tweak: Responsive Bar - Scale added to responsive preview [ED-1209]  (#15169)
* Fix: Promotion - The 'Go Pro' link appears even if the Pro is active [ED-3917] (#15445)
* Tweak: Added option to deep-link into revisions history [ED-3828] (#15503)
* Fix: Dynamic content disappeared if chosen in Code Highlight [ED-3232] (#15357)
* Tweak: Font-Awesome icons as inline SVG [ED-2081] (#15236)
* Tweak: Updating the webpack version to 5.40.0 and Optimizing Frontend JS Files With Babel [ED-3482] (#15507)
* Fix: Dev edition remove beta tag (#15590)
* Fix: The Color Sampler cursor isn't working properly [ED-4128] (#15592)
* Fix: Kit-Library - User is activate and not connected [ED-4154] (#15606)
* Fix:  ScreenShotter timeout (#15608)
* Tweak: Updated Changelog for v3.3.0 (#15609)
* Fix: System Info - User report is enabled even if there is no user [ED-2507] (#14619)
* Fix: Removed deprecated classes calls (#15625)
* Tweak: Additional Breakpoints - System tweaks (ED-3907, ED-3852, ED-3999, ED-3943, ED-4123) (#15465)
* Fix: The build process of 3.4.0 fails. (#15626)
* Tweak - Allowing to create inline-css dependencies between widgets. (#15663)
* Tweak: Adding a back to kit-library button in the import-kit screen [ED-3637] (#15617)
* Fix: The widgets inline CSS was printed as plain text. (#15666)
* Fix: Bug in global widgets cause in 3.3.0 core [ED-4223] (#15691)
* Fix: Z index issues in code highlight dynamic tag icon [ED-4043] (#15696)
* New: CSS Transform [ED-3212] (#15430)
* Tweak: Updated the E-Icons library to 5.12.0 (ED-4222) (#15685)
* Fix: The Inline CSS experiment link is incorrect. (#15687)
* Tweak: Optimizing the Inline CSS dependencies solution [ED-4272] (#15693)
* Fix: Default Mobile width changed to 360px in Responsive Bar preview (#15690)
* Fix: Icon List - Text size increased on Icon list widget with size set to `em` and a link [ED-3177] (#15544)
* Tweak: Updated changelog for v3.3.1 (#15706)
* Revert "Fix: Icon List - Text size increased on Icon list widget with size set to `em` and a link [ED-3177] (#15544)" (#15701)
* Tweak: Improved PHP Lint enforcement [ED-2901] (#15631)
* Fix: Setting zero offset in sticky motion effects not working properly [ED-3514] (#15191)
* Fix: Responsive bar - UI glitch in Responsive bar in Wide screens [ED-4175] (#15647)
* Fix: Lighthouse unused css rules (#15739)
* Fix: The build process fails. (#15726)
* Responsive bar: Fix - Widescreen breakpoint reject values entered in the Responsive bar [ED-4216] (#15689)

#### 3.4.0-dev12 - 2021-07-08
* Tweak: Added support for Additional Breakpoints SASS templates. (#15572)
* Tweak: Fix merge conflicts between Additional Breakpoints and Dev Edition (#15580)
* Fix: When trying to import an Envato kit the UI breaks. (#15582)
* Fix: Image Carousel - Not showing [ED-3640] (#15330)
* Fix: Remote tests don't send back the status [ED-4076] (#15569)
* Tweak: Try to merge all branches and failed only in the end (#15586)

#### 3.4.0-dev11 - 2021-07-07
* Fix: Kit Library - ThemeForest promotion text change [ED-4048] (#15559)
* Fix: Kit file not being created in Multisite WordPress instances [ED-4029] (#15535)
* Tweak: Added custom fields to I/E process [ED-3964] (#15545)
* Fix: Import/Export - Featured images are not imported [ED-3706] (#15553)
* Fix: Kit Library - Connect process not response [ED-4078] (#15561)
* Fix: Closing the import-export wizard was not leading back to the dedicated admin tab [ED-4015] (#15549)
* Tweak - Webpack update preparation. (#15536)
* Fix: Kit-Library - Learn more url [ED-4059] (#15574)

#### 3.4.0-dev10 - 2021-07-05
* Fix: Update lighthouse baseline according to WordPress 5.8 and hello-theme 2.4.0 (#15554)

#### 3.4.0-dev9 - 2021-06-29
* Tweak: The Regenerate-Files admin button should reset all page-assets data [ED-3789] (#15457)
* Tweak: Kit-Library - Errors and Loading UI [ED-3648] (#15460)
* Tweak: Eye Dropper - Changed name to "Color Sampler" [ED-3959] (#15505)
* Tweak: PHP Lint [ED-3960] (#15506)
* Fix: Kit-library - UI fixes [ED-3840] (#15501)
* Fix: Site Identity data was transferred on the Import-Export process [ED-3967] (#15509)
* Fix: Post Excerpt is not imported when applying a kit in Kit Library [ED-3919] (#15510)

#### 3.4.0-dev6 - 2021-06-24
* Tweak: Updating the kit file process text [ED-3938] (#15459)
* Tweak: Additional Breakpoints - Fixes, Widgets and Config adjustments (ED-3894, ED-3935, ED-3934) (#15446)
* Changed "Show Hidden Elements" label (#15461)

#### 3.4.0-dev5 - 2021-06-23
* Fix: Import-Export Dark Mode UI Fixes And Adding Info-Modal Links [ED-3589, ED-3607] (#15382)
* Tweak: Revert the removal of `elementor-widget-wrap` [ED-3746] (#15381)
* Tweak: Allow unlimited export of elementor content (#15397)
* Tweak: Assets Loader Unit Testing [ED-960] (#15300)
* Fix: PHP Lint - Security Linter [ED-3168, ED-2901] (#14948)
* Tweak: Docs - Eye-Dropper [ED-3520] (#15197)
* Tweak: Text-Path & Mask Option documentation [ED-2848] (#14793)
* Tweak: Collapsible Kit Information Section With A Dedicated Info Modal [ED-3620] (#15414)
* Fix: Release and Patch workflows - change log file name (#15412)
* Fix: PHP Lint [ED-3813] (#15422)
*  Select 2: Fix - Control UI glitches in dark mode (#15415)
* Fix: Inline editing not working when the Optimized DOM experiment is on [ED-3081] (#15429)
* Tweak: Controls System Optimization (ED-3378, ED-3643, ED-3744, ED-3814, ED-3839, ED-3855) (#15247)
* Fix: Recreate kit button is not showing when deleting default kit [ED-3843] (#15420)
* Tweak: PHPUNIT - Allow all features to stay registered along the tests lifecycle [ED-3908] (#15439)
* Fix: The kit-library info-modal didn't work due to converting the ModalProvider and Modal components from class to function based. (#15440)
* Tweak: Changing the content-filters to be accessed publicly [ED-3854] (#15434)

#### 3.4.0-dev3 - 2021-06-16
* I/E: Tweak: Force imported home page to be displayed as a front page (#15355)
* Tweak: Throw an error when running import via CLI not as an admin user [ED-3689] (#15358)
* Tweak - Updating the frontend eicons. (#15256)
* Tweak: Kit-Library - button gray color [ED-3728] (#15356)
* Tweak: Admin top bar experiment  turned on only on new sites [ED-3745] (#15360)

#### 3.4.0-dev2 - 2021-06-15
* Fix: Check runs in developer-edition branch [ARCH-7] (#15339)
* Tweak: Kit-Library - Create fallback url [ED-3544] (#15340)
* Fix: Kit-Library - Clear query params removes all the kits [ED-3723] (#15341)
* Tweak: Kit-Library - Updated content and colors on Envato promotion [ED-3722] (#15343)

#### 3.4.0-dev1 - 2021-06-14
* Fix: Share button widget - The mobile alignment doesn't work on safari [ED-3609] (#15272)
* Tweak: Change the admin top bar experiment status to Beta [ED-3606] (#15265)
* Fix: Kit-Library - import not working [ED-3436] (#15280)
* I/E: Change - Kit info is no longer related to default kit data (#15241)
* Fix: Kit-Library - Page doc type [ED-3572] (#15239)
* New: PHP Lint Util functions [ED-3622] (#15277)
* Tweak:  Add "Recreate Kit" button in Elementor settings [ED-3074]  (#15004)
* Tweak: Kit-Library - Add Spinner to apply button [ED-3611] (#15299)
* Fix: Admin top bar doesn't work without Elementor Pro [ED-3661] (#15303)
* Fix: search field in templates categories in wrong location [ED-3592] (#15301)
* Fix: Admin Top Bar - Cancel the loading time of admin top bar [ED-3594] (#15285)
* Fix: Kits - Prevent recreate kit when kit not exists [ED-2394] (#15224)
* Fix: top bar experiment breaks location of elementor notices [ED-3605] (#15273)
* Fix: My Elementor should open in a new tab [ED-3595] (#15269)
* Tweak: Recreate Kit - Catch errors and show them [ED-3652] (#15305)
* Tweak: Kit-Library - Changed back button behavior [ED-3618] (#15318)
* Fix: Inline editing not working when the Optimized DOM experiment is on [ED-3081] (#15261)
* Tweak: Kit-Library - Added Envato promotion [ED-3608] (#15326)
* Tweak: Github - Update whitelist (#15323)
* New: Introducing Kits Library - Create Entire Websites Faster Than Ever [ED-1807] (#15332)
*  Fix: Publish beta workflow [ARCH-34] (#15333)
* Tweak: Improved PHP Lint enforcement [ED-2901] (#15334)
* Revert "Merge pull request #13912 from iNewLegend/ED-1334-custom-fonts-file-urls-are-stati" (#15331)
* Fix: Utils - Return back the `replace-urls` filter [ED-3659] (#15338)

#### 3.3.0-dev15 - 2021-06-08
* Fix: Kit-Library - Using function that not exists in WP 5.6 [ED-3564] (#15235)
* Fix: Kit-Library - Separate kit access level from template access level [ED-3565] (#15238)
* New: Kit-Library - Tests [ED-3135] (#15113)
* Fix: Problems with columns in DOM experiment [ED-3539] (#15232)
* Experiment: Install local testing env [ED-2735] (#14862)
* Fix: Kit-Library - Taxonomies disappear [ED-3574] (#15246)

#### 3.3.0-dev14 - 2021-06-07
* Tweak: Made Landing Pages module active by default (#15221)
* Tweak: Made Landing Pages module active by default (#15221)
* New: Added Elementor top bar to WP-Admin Elementor screens [ED-2797] (#14863)
* Tweak: Changed the status of the eyedropper experiment to "Beta" [ED-3535] (#15223)
* Tweak: Removing Elementor temp uploads folder on each upgrade (#15226)
* Fix: Reversed animations flicker sometimes (#15225)
* Tweak: Kit-Library - production url [ED-3440] (#15147)
* Tweak: Update lhci to 0.8.0 (#15220)
* Tweak: Kit-Library - Styling dark mode [ED-3479] (#15179)

#### 3.3.0-dev13 - 2021-06-04
* Fix: Gradient control doesn't work in Editor when using Global Colors [ED-2292] (#15193)
* Fix: Import-Export - Import only site-settings + previous button in import-content screen [ED-3513] (#15188)
* Tweak: Making the animations CSS library to be loaded conditionally only when needed [ED-2749] (#15125)
* Experiment: PHPCS via Composer [ED-3182] (#14898)

#### 3.3.0-dev12 - 2021-06-02
* Tweak: Added responsive capabilities to Content width in Section and Inner Section [ED-2504] (#14933)
* Tweak: If the user is coming from kit library, some featured image will not imported (#15173)
* Tweak: Usage - Include non elementor pages [ED-3023] (#15005)
* New: Container.js Infra - Find children recursive [ED-2595] (#14500)
* Tweak: Color Picker - Added a tooltip to the eye-dropper icon [ED-2652] (#15154)
* Tweak: Added alt text field when adding an image via URL [ED-3458] (#15167)
* Tweak: Preferences - Add "Responsive preview" title in Preferences for responsive control [ED-3433] (#15150)
* Sortable: Fix - Unable to drag and drop sections one above other [ED-2374] (#15178)
* Tweak: Added `elementor-widget-wrap` to DOM optimization experiment [ED-2259] (#14442)

#### 3.3.0-dev11 - 2021-06-01
* Tweak: Kit-library - Made the filter tags clickable [ED-2904] (#14923)
* Tweak: Kit-Library - favorite kits empty state screen [ED-3131] (#14980)
* Tweak: Kit-Library - not loaded if import-export experiment is off [ED-3335] (#15058)
* Fix: Kit-Library - Move components from app to kit-library [ED-3410] (#15115)
* New: Import Export Phase 2 [ED-2672] (#15124)
* Tweak: Assets Loading Improvements [ED-2511] (#15123)
* Tweak: Hidden Elements Behavior - Add the ability to select hidden elements behavior [ED-2963] (#14901)
* Fix: Kit-Library - Pages section not exists [ED-3416] (#15145)
* Fix: Fetching colors from links using eyedropper opens the link [ED-3178]  (#15143)
* Tweak: Added gradient button capabilities to all button instances [ED-2902] (#14779)
* Tweak: Eyedropper displays explanation tooltip when no colors found [ED-3330] (#15144)
* Tweak: System Info Reporters - Can't mock `get_plugins()` for testing [ED-3439] (#15148)
* Fix: Clicking the eyedropper no longer hides the navigator [ED-2653] (#15142)
* Fix: Panel search bar appeared above the loader [ED-2787] (#14776)
* Tweak: Add Internal and ignore Internal PR from change-log (#15156)
* Fix: Eye Dropper not working on certain panel areas [ED-3411] (#15119)
* Fix: UI glitch in the empty state of Global tab in the Editor Panel [ED-2821] (#14984)
* Tweak: Took change-log from readme.txt for patch and stable versions [ARCH-35] (#15160)
* Tweak: Stop run actions on forks and run PHP 8 + WordPress master only weekly [ARCH-29] (#15157)

#### 3.3.0-dev10 - 2021-05-28
* Tweak: Kit-Library - Send nonce and referrer to the import process [ED-3252] (#15110)
* Tweak: Adding an import-export info popups and kit information [ED-2881] (#15112)
* Fix: Kit info was not updated correctly [ED-3408] (#15117)
* Fix: Can't fetch colors from most widgets using Eye Dropper [ED-3179] (#14979)
* Tweak: Widgets search accepts non-english strings [ED-3145] (#15050)

#### 3.3.0-dev9 - 2021-05-27
* Tweak: Adding summary screens and allowing import content selection [ED-2580] (#14915)
* Fix - Lightbox is not working in the frontend. (#15087)
* Responsive Behavior: Tweak - Made `elementorFrontend.getDeviceSetting()` method dynamic. (#14992)
* Tweak: Responsive Preview Add default height to Tablet and Mobile devices [ED-2425] (#14995)
* Tweak: Responsive Bar - UI modifications for Responsive bar [ED-2721] (#15010)
* Fix: Kit-Library - Sort authentication headers a-z [ED-3380] (#15084)
* Fix: Kit-Library - Rejects from spec [ED-3357] (#15081)
* Lightbox: Check for valid URL [ED-3326] (#15052)
* Tweak: Adding an resolver screen for templates conflicts when importing a kit [ED-3120] (#15066)
* Tweak: Lightbox tweak (ED-3326) (#15096)
* Tweak: Merged release 3.3.0 into feature/responsive-bar (#15102)
* Tweak: Google Maps Widget - Added support for the new Google Maps Embed API (ED-3394) (#15090) (#15099)
* Tweak: Merge Conflict (#15104)
* Fix: Missing Connect & Activate button on the bottom bar. (#15067)

#### 3.3.0-dev8 - 2021-05-25
* Tweak: Kit-Library - Text Fixes [ED-3130] (#15024)
* Compatibility: Added basic 3rd parties compatibility mechanism + Envato templates kits import compatibility (#15029)
* New: I/E WP CLI Commands [ED-3119] (#14941)
* Fix: Kit-library - Re add react-query (#15030)
* New: Media Control - Attach media from URL [ED-1733] (#13612)
* Revert: Auto merge next release into feature branch (#15051)
* Tweak: Kit-Library - sort by featured [ED-3251] (#15057)
* Fix: widget search accepts non-english strings -fixes [ED-3145] (#15056)
* Submissions: New - Promotion [ED-2153] (#14416)
* Fix: Required checks for build dev edition version [ARCH-30] (#15060)
* Tweak: Kit-Library - Add additional connect info to the api [ED-3360] (#15070)
* New: Eslint - Check translations in JSX files [ED-3235] (#15055)
* Fix: Template-Library - Error when cannot import template [ED-3072] (#15023)
* New: Upgrades - Recalculate elements usage each upgrade [ED-2512] (#14632)
* Fix: Move 'schemas' to test folder [ED-3229] (#14930)
* Fix: QUnit - Rearrange parents/modules/tests names [ED-2262] (#14331)
* Tweak: Moving the widgets-CSS reset-action from the assets-loader to the widgets-manager [ED-3084] (#14926)

#### 3.3.0-dev7 - 2021-05-21
* Fix: JIRA deployment info missing pipeline-id and broken run url [ARCH-31] (#15022)
* Tweak: Compatibility-Tag - Changed "Missing Header" text [ED-2804] (#15019)
* Deprecations: Removed deprecated methods. (#14986)
* New: Document - A new filter before save data [ED-3102] (#14824)

#### 3.3.0-dev6 - 2021-05-20
* Tweak: Run lighthouse tests on WordPress master branch instead of version 5.6 (#14997)
* Fix: Finder incorrectly identifies pages created [ED-1269] (#12550)
* Tweak: Report to JIRA deployment info on release/patch/beta/dev flows (#15007)
* Tweak: Kit-Library - Clicking a page in the kit overview opens live preview [ED-3134] (#15006)
* Tweak: Auto merge next release branch into feature branch [ARCH-30] (#14998)
* Fix: Auto merge next release into feature branch (#15016)

#### 3.3.0-dev5 - 2021-05-18
* Fix: Elementor CI Tests will be triggered only on the master repository [ED-3122] (#14820)
* Tweak: Schedule developer-edition release nightly [ARCH-7] (#14860)
* Tweak: Change developer-edition schedule release to 10AM UTC [ARCH-7] (#14866)
* Tweak: Add PR name linter [ARCH-7] (#14867)
* FontAwesome Library: Update thw FontAwesome Library to version 5.15.3 (#14861)
* New: Kit-Library - Favorites [ED-2598] (#14650)
* Fix: Kit-Library - Taxonomies new API scheme [ED-3143] (#14864)
* Icon library update text: Fix - The CTA text in the New Icon Library popup didn't matched the buttons (#14903)
* Fix: Page elements iteration breaks when a template element is missing. (#14921)
* Fix: Mark files for publish to WordPress.org SVN [ARCH-7] (#14916)
* I/E: Change - Added site settings summary titles (#14929)
* Fix: An extra space in the assets iteration action [ED-3215]. (#14939)
* Fix: Eyedropper not working on Chromium based browsers [ED-2960] (#14762)
* Tweak: Kit-Library - Sorting [ED-2597] (#14737)
* Fix: Add/delete from SVN command when nothing changed [ARCH-7] (#14943)

#### 3.3.0-dev4 - 2021-05-09
* Editor/Navigator: Fix - Resize not working when navigator is docked (ED-2526) (#14462)
* WIdgets/Button: Fix - Gradient not working with global colors (ED-2292) (#14484)
* Fix: Dynamic tags for text not working in Text-Path widget. (#14525)
* Fix: JS-QUnit workflow tests - ping timeout (#14562)
* Panel/Feature - New: Color Picker (ED-1958) (#13842)
* ARCH-19 update lhci to 0.7.1 (#14556)
* Tweak: Add GitHub actions workflows for sync feature branches [ARCH-7] (#14608)
* Kit-Library: New - Init (#14184)
* Breakpoints: Fix - Changed the minimum breakpoint value of mobile to 320 (#14625)
* Responsive Bar: Fix - The close button didn't work in desktop mode (#14627)
* Connect: Fix - don't show logo in admin notices (#14626)
* Navigator: Fix - Navigator item's context menu appears behind the navigator itself. (#14606)
* Feature/import export phase 2 [ED-2722] (#14621)
* Feature/import export phase 2 (#14628)
* Site Settings: Fix - 3.2.0 upgrade script injects default values to the kit. (#14633)
* Fix/responsive bar (#14637)
* Fix two bugs in social icons widget [ED-2014 , ED-1917] (#14590)
* Revert "Revert "Remove temporary compatibility for share buttons"" (#14640)
* Feature/import export phase 2 - Added Kits Library integration [ED-2636] (#14644)
* Feature/import export (#14645)
* Fix: Add more log to sync branches script [ARCH-7] (#14654)
* Panel/Feature - New: Color Picker (ED-1958) (#13842) (#14620)
* Modules/Connect: Fix - Base-app, force return code to be int. (#14649)
* Add @iNewLegend to CODEOWNERS ED-2651 (#13512)
* Media Control: Fix - Strings were translated incorrectly in some languages (#14687)
* Revert "Media Control: Fix - Strings were translated incorrectly in some languages (#14687)" (#14692)
* Fix: Section Style tab panel is grayed in several site languages. (#14693)
* Tweak: Add GitHub actions workflows for releases [ARCH-7] (#14520)
* Frontend Assets Loader: Fix - Using more than one dynamic asset in optimized mode caused a JS error [ED-2828] (#14694)
* Responsive Preview: Fix - Resizing preview was broken with old versions of jQuery (#14705)
* Assets Loader: Tweak - Conditional assets registration should be done as part of the page saving process - ED-2077 (#14324)
* PHPUnit/Modules/Usage: Fix - Tests to the convection. (#14713)
* Fix: Kit-Library - Return access_level from connect process. (#14714)
*  New: Kit-Library - Connect to download link. [ED-2712] (#14652)
* JS API/Hooks: Fix - Moving last standing column to another section, cause empty section ED-1072 (#13753)
* Fix: Slides widget not working. (#14617)
* Fix: Activation bug for IDN domains (ED-886) (#14707)
* POC: Language transform in elements panel search. (#14730)
* Document: Tweak - The elements iteration might also be triggered by other processes regardless of any specific experiment - ED-2895 (#14716)
* Tweak: Add slack notify on sync fail [ARCH-27] (#14739)
* E-icons: Fix - When using custom breakpoints, the e-icons font-face URL was called from the custom-frontend CSS file that is located in a different path - ED-2903 (#14740)
* E-icons: Fix - It was not possible to deregister the e-icons fonts. (#14741)
* Tweak: Responsive Bar - [ED-1209] Scale added to responsive preview (#14710)
* Fix: GitHub actions runs url of Slack notification on sync branches failed (#14756)
* New: Core usage schema in schemas/usage - Initial schema. ED-2168 (#14545)
* Fixed: Eyedropper experiment not working on Chromium based browsers (ED-2960) (#14754)
* New: Add remote Elementor CI Tests [ED-2305] (#14641)
* Revert "Fix: Activation bug for IDN domains (ED-886) (#14707)" (#14784)
*  Fix: Activation bug for IDN domains [ED-886] (#14786)
* Widgets CSS: Tweak - Each widget should load its own CSS only when needed - ED-1885 (#14232)
* Edidor Preview: Fix - Minimize the editor window caused to page content to collapsed (#14795)
* Custom Breakpoints: Fix - When using custom breakpoints, responsive custom CSS is not properly generated (ED-3004) (#14794)
* Site Settings: Breakpoints - Added a validator for the breakpoint controls (ED-2605) (#14631)
* Tweak: Enable SVN commands + add copy from trunk to tag on update readme file (#14818)
* Tweak: Generate change-log automatically and add trigger developer-edition on patch release (#14819)
* Default Responsive: Tweak - Added default responsive device when responsive view launched [2719] (#14821)
* Tweak: Update google fonts, close #14732 (#14844)
* Fix: When saving a page and the optimized mode is inactive, the page assets data is not being updated in case that data was saved at least once [ED-3082] (#14823)
* Basic Gallery Widget: Fix - Disable lightbox does not affect the Basic Gallery widget (#12913). (#14813)
* Fix: Cannot add dynamic tags [ED-3140] (#14859)
