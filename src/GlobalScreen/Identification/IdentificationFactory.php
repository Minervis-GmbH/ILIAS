<?php namespace ILIAS\GlobalScreen\Identification;

/**
 * Class IdentificationFactory
 *
 * All elements in the GlobalScreen service must be identifiable for the supplying
 * components mentioned in the readme. The GlobalScreen service uses this identification, for
 * example, for parent/child relationships. The identification is also forwarded
 * to the UI service or to the instance that then renders the GlobalScreen elements. This
 * means that the identification can be used there again, for example, to
 * generate unique IDs for the online help.
 *
 * There will be at least two IdentificationProvider, one for core components
 * and one for plugins. This factory allows to acces both.
 *
 * The identification you get can be serialized and is used e.g. to store in
 * database and cache. you don't need to take care of storing this.
 *
 * Since you are passing some identifiers as a string such as 'personal_desktop'
 * the GlobalScreen-Services must take care after naming collisions. Therefore you always
 * pass your Provider (or even the Plugin-Class in case of Plugins) and the GlobalScreen-
 * Services will use this information to generate unique identifications.
 *
 * Currently Identifications are only used for the GlobalScreen-MainMenu-Elements.
 * Other like Footer may follow.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class IdentificationFactory {

	/**
	 * Returns a IndentificationProvider for core components, only a Provider
	 * is needed.
	 *
	 * @param \ILIAS\GlobalScreen\Provider\Provider $provider
	 *
	 * @return IdentificationProviderInterface
	 */
	public function core(\ILIAS\GlobalScreen\Provider\Provider $provider): IdentificationProviderInterface {
		return new CoreIdentificationProvider(get_class($provider));
	}


	/**
	 * Returns a IndentificationProvider for ILIAS-Plugins which takes care of
	 * the plugin_id for further identification where a provided GlobalScreen-element
	 * comes from (e.g. to disable or delete all elements when a plugin is
	 * deleted or deactivated).
	 *
	 * @param \ilPlugin                             $plugin
	 * @param \ILIAS\GlobalScreen\Provider\Provider $provider
	 *
	 * @return IdentificationProviderInterface
	 */
	public function plugin(\ilPlugin $plugin, \ILIAS\GlobalScreen\Provider\Provider $provider): IdentificationProviderInterface {
		return new PluginIdentificationProvider(get_class($provider), $plugin->getId());
	}
}
