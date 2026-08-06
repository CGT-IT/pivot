/**
 * Client-side search on Pivot listing pages.
 *
 * Progressive enhancement: the filter form is a plain GET form pointing at the
 * listing page, so it keeps working without JavaScript — the server renders the
 * filtered results as it always did. When this script runs, submitting the form
 * fetches the results from the REST endpoint and swaps them in, keeping the URL in
 * sync so the search stays shareable, bookmarkable and navigable with the back button.
 */
(function () {
  'use strict';

  var config = window.pivotListing;
  if (!config || !config.endpoint) {
    return;
  }

  var form = document.getElementById('pivot-filter-form');
  var resultsArea = document.getElementById('offers-area');
  if (!form || !resultsArea) {
    return;
  }

  var offersContainer = resultsArea.querySelector('.row') || resultsArea;
  var countNode = document.querySelector('[data-pivot-count]');
  var paginationNode = document.querySelector('[data-pivot-pagination]');
  var inFlight = null;

  /**
   * Active filters, read straight off the form.
   *
   * @return {Object} filter id => value
   */
  function collectFilters() {
    var filters = {};
    var pattern = new RegExp('^' + config.param + '\\[(\\d+)\\]$');

    Array.prototype.forEach.call(form.elements, function (element) {
      if (!element.name) {
        return;
      }
      var match = element.name.match(pattern);
      if (!match) {
        return;
      }
      if (element.type === 'checkbox') {
        if (element.checked) {
          filters[match[1]] = 'on';
        }
        return;
      }
      if (element.value !== '' && element.value !== 'all') {
        filters[match[1]] = element.value;
      }
    });

    return filters;
  }

  /**
   * Build the public URL matching a search, so history entries stay meaningful.
   */
  function buildUrl(filters, page) {
    var params = [];

    Object.keys(filters).forEach(function (id) {
      params.push(
        encodeURIComponent(config.param + '[' + id + ']') + '=' + encodeURIComponent(filters[id])
      );
    });
    if (page > 1) {
      params.push('paged=' + page);
    }

    return params.length ? config.pageUrl + '?' + params.join('&') : config.pageUrl;
  }

  function buildEndpoint(filters, page) {
    var params = ['paged=' + page];

    Object.keys(filters).forEach(function (id) {
      params.push('filters[' + encodeURIComponent(id) + ']=' + encodeURIComponent(filters[id]));
    });

    return config.endpoint + '?' + params.join('&');
  }

  function renderCount(total) {
    if (!countNode) {
      return;
    }
    var template = total === 1 ? config.i18n.countOne : config.i18n.countMany;
    countNode.textContent = template.replace('%s', String(total));
  }

  /**
   * Rebuild the pager. Links go through the same fetch path rather than reloading.
   */
  function renderPagination(filters, page, pages) {
    if (!paginationNode) {
      return;
    }
    if (pages < 2) {
      paginationNode.innerHTML = '';
      return;
    }

    var list = document.createElement('ul');
    list.className = 'pagination justify-content-center m-0';

    for (var i = 1; i <= pages; i++) {
      var item = document.createElement('li');
      item.className = 'page-item' + (i === page ? ' active' : '');

      var link = document.createElement('a');
      link.className = 'page-link';
      link.href = buildUrl(filters, i);
      link.textContent = String(i);
      link.setAttribute('data-pivot-page', String(i));

      item.appendChild(link);
      list.appendChild(item);
    }

    paginationNode.innerHTML = '';
    paginationNode.appendChild(list);
  }

  /**
   * Fetch and display one page of results.
   *
   * @param {Object} filters
   * @param {number} page
   * @param {boolean} pushState False when replaying a history entry.
   */
  function load(filters, page, pushState) {
    if (inFlight) {
      inFlight.abort();
    }

    resultsArea.setAttribute('aria-busy', 'true');
    resultsArea.classList.add('pivot-loading');

    var request = new XMLHttpRequest();
    inFlight = request;
    request.open('GET', buildEndpoint(filters, page), true);
    request.setRequestHeader('Accept', 'application/json');

    request.onload = function () {
      inFlight = null;
      resultsArea.removeAttribute('aria-busy');
      resultsArea.classList.remove('pivot-loading');

      if (request.status < 200 || request.status >= 300) {
        offersContainer.innerHTML = '<p class="alert alert-warning">' + config.i18n.error + '</p>';
        return;
      }

      var payload;
      try {
        payload = JSON.parse(request.responseText);
      } catch (e) {
        offersContainer.innerHTML = '<p class="alert alert-warning">' + config.i18n.error + '</p>';
        return;
      }

      offersContainer.innerHTML = payload.html || '<p class="alert alert-info">' + payload.empty + '</p>';
      renderCount(payload.total);
      renderPagination(filters, payload.page, payload.pages);

      if (pushState) {
        window.history.pushState({ filters: filters, page: page }, '', buildUrl(filters, page));
      }

      // Let the map and any other listener know the offer list changed.
      document.dispatchEvent(new CustomEvent('pivot:offersUpdated', { detail: payload }));
    };

    request.onerror = function () {
      inFlight = null;
      resultsArea.removeAttribute('aria-busy');
      resultsArea.classList.remove('pivot-loading');
      offersContainer.innerHTML = '<p class="alert alert-warning">' + config.i18n.error + '</p>';
    };

    request.send();
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    load(collectFilters(), 1, true);
  });

  var reset = document.getElementById('filter-reset');
  if (reset) {
    reset.addEventListener('click', function (event) {
      event.preventDefault();
      form.reset();
      Array.prototype.forEach.call(form.querySelectorAll('input[type="checkbox"]'), function (box) {
        box.checked = false;
      });
      load({}, 1, true);
    });
  }

  // Pagination links are rendered by the server on first paint and by this script
  // afterwards, so the handler is delegated.
  document.addEventListener('click', function (event) {
    var link = event.target.closest ? event.target.closest('[data-pivot-page]') : null;
    if (!link || !paginationNode || !paginationNode.contains(link)) {
      return;
    }
    event.preventDefault();
    load(collectFilters(), parseInt(link.getAttribute('data-pivot-page'), 10) || 1, true);
  });

  window.addEventListener('popstate', function (event) {
    var state = event.state;
    if (!state) {
      return;
    }
    load(state.filters || {}, state.page || 1, false);
  });
})();
