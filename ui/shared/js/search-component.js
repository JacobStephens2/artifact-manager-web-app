// Reusable search component for the artifact management tool
//
// Provides a configurable typeahead / autocomplete pattern that works for both
// artifact search and user/player search. It handles:
//   - Debounced input handling (avoids flooding the API on every keystroke)
//   - Rendering a dropdown list of results
//   - Selection callbacks
//   - Show / hide logic
//
// Usage (artifact search):
//
//   import SearchComponent from '/shared/js/search-component.js';
//   import ApiClient from '/shared/js/api-client.js';
//
//   const search = SearchComponent.create({
//     inputSelector:   'input#SearchTitles',
//     resultsSelector: 'ul.searchResults',
//     wrapperSelector: 'div.searchResults',
//     fetchResults: async (query) => {
//       const data = await ApiClient.searchArtifacts(query, userId);
//       return data.artifacts.map(a => ({ id: a.id, label: a.Title }));
//     },
//     onSelect: (item) => {
//       document.querySelector('input#SearchTitleSubmission').value = item.id;
//       document.querySelector('input#SearchTitles').value = item.label;
//     },
//     maxResults: 10,
//     debounceMs: 250,
//   });
//
// Usage (user search):
//
//   const userSearch = SearchComponent.create({
//     inputSelector:   'input#user0name',
//     resultsSelector: 'ul#userResults0',
//     wrapperSelector: 'div#userResultsDiv0',
//     fetchResults: async (query) => {
//       const data = await ApiClient.searchUsers(query, currentUserId);
//       return data.users.map(u => ({
//         id: u.id,
//         label: `${u.FirstName} ${u.LastName}`,
//       }));
//     },
//     onSelect: (item) => {
//       document.querySelector('input#user0id').value = item.id;
//       document.querySelector('input#user0name').value = item.label;
//     },
//   });

const SearchComponent = {

  /**
   * Create and bind a new search component to the DOM.
   *
   * @param {object} config
   * @param {string}   config.inputSelector    - CSS selector for the text input
   * @param {string}   config.resultsSelector   - CSS selector for the <ul> that holds results
   * @param {string}   config.wrapperSelector   - CSS selector for the wrapper <div> (toggled visible/hidden)
   * @param {Function} config.fetchResults       - async (query: string) => Array<{ id, label }>
   * @param {Function} config.onSelect           - (item: { id, label }) => void
   * @param {number}   [config.maxResults=10]    - Maximum number of results to display
   * @param {number}   [config.debounceMs=200]   - Debounce interval in milliseconds
   * @param {string}   [config.hideOnFocusSelector] - Optional selector; focusing this element hides results
   * @returns {object} A controller with destroy() to unbind listeners
   */
  create(config) {
    const {
      inputSelector,
      resultsSelector,
      wrapperSelector,
      fetchResults,
      onSelect,
      maxResults = 10,
      debounceMs = 200,
      hideOnFocusSelector = null,
    } = config;

    const inputEl   = document.querySelector(inputSelector);
    const listEl    = document.querySelector(resultsSelector);
    const wrapperEl = document.querySelector(wrapperSelector);

    if (!inputEl || !listEl || !wrapperEl) {
      console.warn(
        '[SearchComponent] Could not find one or more required elements:',
        { inputSelector, resultsSelector, wrapperSelector }
      );
      return { destroy() {} };
    }

    let debounceTimer = null;
    let abortController = null;

    // -- visibility helpers ---------------------------------------------------

    function show() {
      wrapperEl.style.display = 'block';
    }

    function hide() {
      wrapperEl.style.display = 'none';
    }

    function clear() {
      listEl.innerHTML = '';
      hide();
    }

    // -- core search handler --------------------------------------------------

    async function handleInput(event) {
      const query = event.target.value.trim();

      // Clear previous debounce
      if (debounceTimer) {
        clearTimeout(debounceTimer);
      }

      // Abort any in-flight request from a previous keystroke
      if (abortController) {
        abortController.abort();
        abortController = null;
      }

      if (query.length === 0) {
        clear();
        return;
      }

      debounceTimer = setTimeout(async () => {
        abortController = new AbortController();

        try {
          const results = await fetchResults(query, abortController.signal);
          renderResults(results);
        } catch (err) {
          // AbortError is expected when a newer keystroke cancels the previous one
          if (err.name !== 'AbortError') {
            console.error('[SearchComponent] fetch error:', err);
          }
        }
      }, debounceMs);
    }

    // -- rendering ------------------------------------------------------------

    function renderResults(results) {
      listEl.innerHTML = '';

      if (!results || results.length === 0) {
        hide();
        return;
      }

      const displayCount = Math.min(results.length, maxResults);

      for (let i = 0; i < displayCount; i++) {
        const item = results[i];
        const li = document.createElement('li');
        li.textContent = item.label;
        li.dataset.id = item.id;

        li.addEventListener('click', () => {
          onSelect(item);
          clear();
        });

        listEl.appendChild(li);
      }

      show();
    }

    // -- bind events ----------------------------------------------------------

    inputEl.addEventListener('input', handleInput);
    inputEl.addEventListener('focus', () => {
      // Re-show results if the list already has children (user tabbed away and back)
      if (listEl.children.length > 0) {
        show();
      }
    });

    // Optional: hide results when a different element receives focus
    let hideOnFocusEl = null;
    if (hideOnFocusSelector) {
      hideOnFocusEl = document.querySelector(hideOnFocusSelector);
      if (hideOnFocusEl) {
        hideOnFocusEl.addEventListener('focus', hide);
      }
    }

    // Close the dropdown when clicking outside
    function handleDocumentClick(event) {
      if (!wrapperEl.contains(event.target) && event.target !== inputEl) {
        hide();
      }
    }
    document.addEventListener('click', handleDocumentClick);

    // -- public controller ----------------------------------------------------

    return {
      /** Show the results dropdown programmatically */
      show,
      /** Hide the results dropdown programmatically */
      hide,
      /** Clear results and hide the dropdown */
      clear,
      /**
       * Remove all event listeners. Call this if the search component is no
       * longer needed (e.g. when removing a dynamically-added user row).
       */
      destroy() {
        if (debounceTimer) {
          clearTimeout(debounceTimer);
        }
        if (abortController) {
          abortController.abort();
        }
        inputEl.removeEventListener('input', handleInput);
        document.removeEventListener('click', handleDocumentClick);
        if (hideOnFocusEl) {
          hideOnFocusEl.removeEventListener('focus', hide);
        }
      },
    };
  },
};

export default SearchComponent;
