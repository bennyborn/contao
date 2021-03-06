<?php

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao;

use Patchwork\Utf8;

/**
 * Front end module "book navigation".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ModuleBooknav extends Module
{
	/**
	 * Pages array
	 * @var PageModel[]
	 */
	protected $arrPages = array();

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_booknav';

	/**
	 * Display a wildcard in the back end
	 *
	 * @return string
	 */
	public function generate()
	{
		$request = System::getContainer()->get('request_stack')->getCurrentRequest();

		if ($request && System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request))
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ' . Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['booknav'][0]) . ' ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		/** @var PageModel $objPage */
		global $objPage;

		if (!$this->rootPage || !\in_array($this->rootPage, $objPage->trail))
		{
			return '';
		}

		return parent::generate();
	}

	/**
	 * Generate the module
	 */
	protected function compile()
	{
		// Get the root page
		if (!($objTarget = $this->objModel->getRelated('rootPage')) instanceof PageModel)
		{
			return;
		}

		$groups = array();

		// Get all groups of the current front end user
		if (System::getContainer()->get('contao.security.token_checker')->hasFrontendUser())
		{
			$this->import(FrontendUser::class, 'User');
			$groups = $this->User->groups;
		}

		// Get all book pages
		$this->arrPages[$objTarget->id] = $objTarget;
		$this->getBookPages($objTarget->id, $groups, time());

		/** @var PageModel $objPage */
		global $objPage;

		// Upper page
		if ($objPage->id != $objTarget->id)
		{
			$intKey = $objPage->pid;

			// Skip forward pages (see #5074)
			while ($this->arrPages[$intKey]->type == 'forward' && isset($this->arrPages[$intKey]->pid))
			{
				$intKey = $this->arrPages[$intKey]->pid;
			}

			// Hide the link if the reference page is a forward page (see #5374)
			if (isset($this->arrPages[$intKey]))
			{
				$this->Template->hasUp = true;
				$this->Template->upHref = $this->arrPages[$intKey]->getFrontendUrl();
				$this->Template->upTitle = StringUtil::specialchars($this->arrPages[$intKey]->title, true);
				$this->Template->upPageTitle = StringUtil::specialchars($this->arrPages[$intKey]->pageTitle, true);
				$this->Template->upLink = $GLOBALS['TL_LANG']['MSC']['up'];
			}
		}

		$arrLookup = array_keys($this->arrPages);
		$intCurrent = array_search($objPage->id, $arrLookup);

		if ($intCurrent === false)
		{
			return; // see #8665
		}

		// HOOK: add pagination info
		$this->Template->currentPage = $intCurrent;
		$this->Template->pageCount = \count($arrLookup);

		// Previous page
		if ($intCurrent > 0)
		{
			$current = $intCurrent;
			$intKey = $arrLookup[($current - 1)];

			// Skip forward pages (see #5074)
			while ($this->arrPages[$intKey]->type == 'forward' && isset($arrLookup[--$current]))
			{
				$intKey = $arrLookup[($current - 1)];
			}

			if ($intKey === null)
			{
				$this->Template->hasPrev = false;
			}
			else
			{
				$this->Template->hasPrev = true;
				$this->Template->prevHref = $this->arrPages[$intKey]->getFrontendUrl();
				$this->Template->prevTitle = StringUtil::specialchars($this->arrPages[$intKey]->title, true);
				$this->Template->prevPageTitle = StringUtil::specialchars($this->arrPages[$intKey]->pageTitle, true);
				$this->Template->prevLink = $this->arrPages[$intKey]->title;
			}
		}

		// Next page
		if ($intCurrent < (\count($arrLookup) - 1))
		{
			$current = $intCurrent;
			$intKey = $arrLookup[($current + 1)];

			// Skip forward pages (see #5074)
			while ($this->arrPages[$intKey]->type == 'forward' && isset($arrLookup[++$current]))
			{
				$intKey = $arrLookup[($current + 1)];
			}

			if ($intKey === null)
			{
				$this->Template->hasNext = false;
			}
			else
			{
				$this->Template->hasNext = true;
				$this->Template->nextHref = $this->arrPages[$intKey]->getFrontendUrl();
				$this->Template->nextTitle = StringUtil::specialchars($this->arrPages[$intKey]->title, true);
				$this->Template->nextPageTitle = StringUtil::specialchars($this->arrPages[$intKey]->pageTitle, true);
				$this->Template->nextLink = $this->arrPages[$intKey]->title;
			}
		}
	}

	/**
	 * Recursively get all book pages
	 *
	 * @param integer $intParentId
	 * @param array   $groups
	 * @param integer $time
	 */
	protected function getBookPages($intParentId, $groups, $time)
	{
		$arrPages = static::getPublishedSubpagesWithoutGuestsByPid($intParentId, $this->showHidden);

		if ($arrPages === null)
		{
			return;
		}

		foreach ($arrPages as list('page' => $objPage, 'hasSubpages' => $blnHasSubpages))
		{
			$_groups = StringUtil::deserialize($objPage->groups);

			// Do not show protected pages unless a front end user is logged in
			if (!$objPage->protected || $this->showProtected || (\is_array($groups) && \is_array($_groups) && \count(array_intersect($groups, $_groups))))
			{
				$this->arrPages[$objPage->id] = $objPage;

				if ($blnHasSubpages)
				{
					$this->getBookPages($objPage->id, $groups, $time);
				}
			}
		}
	}
}

class_alias(ModuleBooknav::class, 'ModuleBooknav');
