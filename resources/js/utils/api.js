/**
 * API fetch wrapper utilities
 * @module utils/api
 */

// Base URL for API requests
const BASE_URL = '/api';

/**
 * Default headers for API requests
 */
const defaultHeaders = {
  'Content-Type': 'application/json',
  'Accept': 'application/json'
};

/**
 * Handle API response
 * @param {Response} response - Fetch response object
 * @returns {Promise<object>} Parsed response data
 */
async function handleResponse(response) {
  const data = await response.json().catch(() => null);
  
  if (!response.ok) {
    const error = new Error(data?.message || `HTTP error ${response.status}`);
    error.status = response.status;
    error.data = data;
    throw error;
  }
  
  return data;
}

/**
 * Make a GET request
 * @param {string} endpoint - API endpoint
 * @param {object} params - Query parameters
 * @returns {Promise<object>} Response data
 */
export async function get(endpoint, params = {}) {
  const url = new URL(`${BASE_URL}${endpoint}`, window.location.origin);
  Object.keys(params).forEach(key => {
    if (params[key] !== undefined && params[key] !== null) {
      url.searchParams.append(key, params[key]);
    }
  });
  
  const response = await fetch(url.toString(), {
    method: 'GET',
    headers: defaultHeaders
  });
  
  return handleResponse(response);
}

/**
 * Make a POST request
 * @param {string} endpoint - API endpoint
 * @param {object} body - Request body
 * @returns {Promise<object>} Response data
 */
export async function post(endpoint, body = {}) {
  const response = await fetch(`${BASE_URL}${endpoint}`, {
    method: 'POST',
    headers: defaultHeaders,
    body: JSON.stringify(body)
  });
  
  return handleResponse(response);
}

/**
 * Make a PUT request
 * @param {string} endpoint - API endpoint
 * @param {object} body - Request body
 * @returns {Promise<object>} Response data
 */
export async function put(endpoint, body = {}) {
  const response = await fetch(`${BASE_URL}${endpoint}`, {
    method: 'PUT',
    headers: defaultHeaders,
    body: JSON.stringify(body)
  });
  
  return handleResponse(response);
}

/**
 * Make a PATCH request
 * @param {string} endpoint - API endpoint
 * @param {object} body - Request body
 * @returns {Promise<object>} Response data
 */
export async function patch(endpoint, body = {}) {
  const response = await fetch(`${BASE_URL}${endpoint}`, {
    method: 'PATCH',
    headers: defaultHeaders,
    body: JSON.stringify(body)
  });
  
  return handleResponse(response);
}

/**
 * Make a DELETE request
 * @param {string} endpoint - API endpoint
 * @returns {Promise<object>} Response data
 */
export async function del(endpoint) {
  const response = await fetch(`${BASE_URL}${endpoint}`, {
    method: 'DELETE',
    headers: defaultHeaders
  });
  
  return handleResponse(response);
}

export default {
  get,
  post,
  put,
  patch,
  delete: del
};
