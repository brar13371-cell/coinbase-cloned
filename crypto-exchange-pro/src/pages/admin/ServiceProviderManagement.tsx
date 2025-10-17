import React, { useState, useEffect } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { RootState } from '../../store';
import { 
  PlusIcon, 
  PencilIcon, 
  TrashIcon, 
  PlayIcon, 
  PauseIcon, 
  EyeIcon,
  CheckCircleIcon,
  XCircleIcon,
  ExclamationTriangleIcon,
  ServerIcon,
  CogIcon,
  ChartBarIcon
} from '@heroicons/react/24/outline';
import { apiService } from '../../services/api';

interface ServiceProvider {
  id: number;
  name: string;
  type: string;
  subtype?: string;
  is_enabled: boolean;
  is_active: boolean;
  priority: number;
  weight: number;
  health_status: 'healthy' | 'unhealthy' | 'unknown' | 'maintenance';
  health_score: number;
  last_health_check?: string;
  success_count: number;
  error_count: number;
  total_requests: number;
  rate_limit: number;
  timeout: number;
  retry_attempts: number;
  api_endpoint?: string;
  created_at: string;
  updated_at: string;
}

const ServiceProviderManagement: React.FC = () => {
  const dispatch = useDispatch();
  const { user } = useSelector((state: RootState) => state.auth);
  const [providers, setProviders] = useState<ServiceProvider[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showModal, setShowModal] = useState(false);
  const [editingProvider, setEditingProvider] = useState<ServiceProvider | null>(null);
  const [formData, setFormData] = useState({
    name: '',
    type: '',
    subtype: '',
    api_endpoint: '',
    api_key: '',
    api_secret: '',
    priority: 0,
    weight: 1.0,
    rate_limit: 1000,
    timeout: 30,
    retry_attempts: 3,
    config: {}
  });

  useEffect(() => {
    fetchProviders();
  }, []);

  const fetchProviders = async () => {
    try {
      setLoading(true);
      const response = await apiService.get('/admin/god/service-providers');
      if (response.success) {
        setProviders(response.data);
      } else {
        setError(response.message || 'Failed to fetch service providers');
      }
    } catch (err) {
      setError('Failed to fetch service providers');
    } finally {
      setLoading(false);
    }
  };

  const handleCreateProvider = async () => {
    try {
      const response = await apiService.post('/admin/god/service-providers', {
        action: 'create',
        ...formData
      });
      
      if (response.success) {
        setShowModal(false);
        setFormData({
          name: '',
          type: '',
          subtype: '',
          api_endpoint: '',
          api_key: '',
          api_secret: '',
          priority: 0,
          weight: 1.0,
          rate_limit: 1000,
          timeout: 30,
          retry_attempts: 3,
          config: {}
        });
        fetchProviders();
      } else {
        setError(response.message || 'Failed to create service provider');
      }
    } catch (err) {
      setError('Failed to create service provider');
    }
  };

  const handleUpdateProvider = async () => {
    if (!editingProvider) return;
    
    try {
      const response = await apiService.post('/admin/god/service-providers', {
        action: 'update',
        provider_id: editingProvider.id,
        ...formData
      });
      
      if (response.success) {
        setShowModal(false);
        setEditingProvider(null);
        setFormData({
          name: '',
          type: '',
          subtype: '',
          api_endpoint: '',
          api_key: '',
          api_secret: '',
          priority: 0,
          weight: 1.0,
          rate_limit: 1000,
          timeout: 30,
          retry_attempts: 3,
          config: {}
        });
        fetchProviders();
      } else {
        setError(response.message || 'Failed to update service provider');
      }
    } catch (err) {
      setError('Failed to update service provider');
    }
  };

  const handleDeleteProvider = async (providerId: number) => {
    if (!confirm('Are you sure you want to delete this service provider?')) return;
    
    try {
      const response = await apiService.post('/admin/god/service-providers', {
        action: 'delete',
        provider_id: providerId
      });
      
      if (response.success) {
        fetchProviders();
      } else {
        setError(response.message || 'Failed to delete service provider');
      }
    } catch (err) {
      setError('Failed to delete service provider');
    }
  };

  const handleToggleProvider = async (providerId: number, enabled: boolean) => {
    try {
      const response = await apiService.post('/admin/god/service-providers', {
        action: enabled ? 'enable' : 'disable',
        provider_id: providerId
      });
      
      if (response.success) {
        fetchProviders();
      } else {
        setError(response.message || 'Failed to toggle service provider');
      }
    } catch (err) {
      setError('Failed to toggle service provider');
    }
  };

  const handleTestProvider = async (providerId: number) => {
    try {
      const response = await apiService.post('/admin/god/service-providers', {
        action: 'test',
        provider_id: providerId
      });
      
      if (response.success) {
        fetchProviders();
      } else {
        setError(response.message || 'Failed to test service provider');
      }
    } catch (err) {
      setError('Failed to test service provider');
    }
  };

  const openEditModal = (provider: ServiceProvider) => {
    setEditingProvider(provider);
    setFormData({
      name: provider.name,
      type: provider.type,
      subtype: provider.subtype || '',
      api_endpoint: provider.api_endpoint || '',
      api_key: '',
      api_secret: '',
      priority: provider.priority,
      weight: provider.weight,
      rate_limit: provider.rate_limit,
      timeout: provider.timeout,
      retry_attempts: provider.retry_attempts,
      config: {}
    });
    setShowModal(true);
  };

  const closeModal = () => {
    setShowModal(false);
    setEditingProvider(null);
    setFormData({
      name: '',
      type: '',
      subtype: '',
      api_endpoint: '',
      api_key: '',
      api_secret: '',
      priority: 0,
      weight: 1.0,
      rate_limit: 1000,
      timeout: 30,
      retry_attempts: 3,
      config: {}
    });
  };

  const getHealthStatusIcon = (status: string) => {
    switch (status) {
      case 'healthy':
        return <CheckCircleIcon className="h-5 w-5 text-green-500" />;
      case 'unhealthy':
        return <XCircleIcon className="h-5 w-5 text-red-500" />;
      case 'maintenance':
        return <ExclamationTriangleIcon className="h-5 w-5 text-yellow-500" />;
      default:
        return <ExclamationTriangleIcon className="h-5 w-5 text-gray-500" />;
    }
  };

  const getHealthStatusColor = (status: string) => {
    switch (status) {
      case 'healthy':
        return 'bg-green-100 text-green-800';
      case 'unhealthy':
        return 'bg-red-100 text-red-800';
      case 'maintenance':
        return 'bg-yellow-100 text-yellow-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="bg-white shadow rounded-lg">
        <div className="px-4 py-5 sm:p-6">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-2xl font-bold text-gray-900">Service Provider Management</h1>
              <p className="mt-1 text-sm text-gray-500">
                Manage all service providers including market data, liquidity, payment, KYC, and more
              </p>
            </div>
            <button
              onClick={() => setShowModal(true)}
              className="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
            >
              <PlusIcon className="h-4 w-4 mr-2" />
              Add Provider
            </button>
          </div>
        </div>
      </div>

      {/* Error Message */}
      {error && (
        <div className="bg-red-50 border border-red-200 rounded-md p-4">
          <div className="flex">
            <XCircleIcon className="h-5 w-5 text-red-400" />
            <div className="ml-3">
              <h3 className="text-sm font-medium text-red-800">Error</h3>
              <div className="mt-2 text-sm text-red-700">{error}</div>
            </div>
          </div>
        </div>
      )}

      {/* Providers Table */}
      <div className="bg-white shadow rounded-lg">
        <div className="px-4 py-5 sm:p-6">
          <div className="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
            <table className="min-w-full divide-y divide-gray-300">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Name
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Type
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Health
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Requests
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Priority
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {providers.map((provider) => (
                  <tr key={provider.id}>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center">
                        <ServerIcon className="h-5 w-5 text-gray-400 mr-3" />
                        <div>
                          <div className="text-sm font-medium text-gray-900">
                            {provider.name}
                          </div>
                          <div className="text-sm text-gray-500">
                            {provider.api_endpoint || 'No endpoint'}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="text-sm text-gray-900">{provider.type}</div>
                      {provider.subtype && (
                        <div className="text-sm text-gray-500">{provider.subtype}</div>
                      )}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center space-x-2">
                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                          provider.is_active 
                            ? 'bg-green-100 text-green-800' 
                            : 'bg-red-100 text-red-800'
                        }`}>
                          {provider.is_active ? 'Active' : 'Inactive'}
                        </span>
                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                          provider.is_enabled 
                            ? 'bg-blue-100 text-blue-800' 
                            : 'bg-gray-100 text-gray-800'
                        }`}>
                          {provider.is_enabled ? 'Enabled' : 'Disabled'}
                        </span>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center">
                        {getHealthStatusIcon(provider.health_status)}
                        <div className="ml-2">
                          <div className={`text-sm font-medium ${
                            provider.health_status === 'healthy' ? 'text-green-900' : 
                            provider.health_status === 'unhealthy' ? 'text-red-900' : 
                            'text-gray-900'
                          }`}>
                            {provider.health_status}
                          </div>
                          <div className="text-sm text-gray-500">
                            {provider.health_score}% score
                          </div>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <div>Total: {provider.total_requests.toLocaleString()}</div>
                      <div>Success: {provider.success_count.toLocaleString()}</div>
                      <div>Errors: {provider.error_count.toLocaleString()}</div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      <div>Priority: {provider.priority}</div>
                      <div>Weight: {provider.weight}</div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                      <button
                        onClick={() => handleTestProvider(provider.id)}
                        className="text-blue-600 hover:text-blue-900"
                        title="Test Connection"
                      >
                        <EyeIcon className="h-4 w-4" />
                      </button>
                      <button
                        onClick={() => openEditModal(provider)}
                        className="text-indigo-600 hover:text-indigo-900"
                        title="Edit"
                      >
                        <PencilIcon className="h-4 w-4" />
                      </button>
                      <button
                        onClick={() => handleToggleProvider(provider.id, !provider.is_enabled)}
                        className={provider.is_enabled ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'}
                        title={provider.is_enabled ? 'Disable' : 'Enable'}
                      >
                        {provider.is_enabled ? <PauseIcon className="h-4 w-4" /> : <PlayIcon className="h-4 w-4" />}
                      </button>
                      <button
                        onClick={() => handleDeleteProvider(provider.id)}
                        className="text-red-600 hover:text-red-900"
                        title="Delete"
                      >
                        <TrashIcon className="h-4 w-4" />
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {/* Modal */}
      {showModal && (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
          <div className="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div className="mt-3">
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-medium text-gray-900">
                  {editingProvider ? 'Edit Service Provider' : 'Add Service Provider'}
                </h3>
                <button
                  onClick={closeModal}
                  className="text-gray-400 hover:text-gray-600"
                >
                  <XCircleIcon className="h-6 w-6" />
                </button>
              </div>
              
              <form className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700">Name</label>
                  <input
                    type="text"
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    required
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700">Type</label>
                  <select
                    value={formData.type}
                    onChange={(e) => setFormData({ ...formData, type: e.target.value })}
                    className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    required
                  >
                    <option value="">Select Type</option>
                    <option value="market_data">Market Data</option>
                    <option value="liquidity">Liquidity</option>
                    <option value="payment">Payment</option>
                    <option value="kyc">KYC</option>
                    <option value="notification">Notification</option>
                    <option value="custody">Custody</option>
                    <option value="blockchain">Blockchain</option>
                    <option value="staking">Staking</option>
                    <option value="lending">Lending</option>
                    <option value="defi">DeFi</option>
                    <option value="nft">NFT</option>
                    <option value="oracle">Oracle</option>
                    <option value="analytics">Analytics</option>
                  </select>
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700">Subtype</label>
                  <input
                    type="text"
                    value={formData.subtype}
                    onChange={(e) => setFormData({ ...formData, subtype: e.target.value })}
                    className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700">API Endpoint</label>
                  <input
                    type="url"
                    value={formData.api_endpoint}
                    onChange={(e) => setFormData({ ...formData, api_endpoint: e.target.value })}
                    className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700">API Key</label>
                  <input
                    type="text"
                    value={formData.api_key}
                    onChange={(e) => setFormData({ ...formData, api_key: e.target.value })}
                    className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700">API Secret</label>
                  <input
                    type="password"
                    value={formData.api_secret}
                    onChange={(e) => setFormData({ ...formData, api_secret: e.target.value })}
                    className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
                
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700">Priority</label>
                    <input
                      type="number"
                      value={formData.priority}
                      onChange={(e) => setFormData({ ...formData, priority: parseInt(e.target.value) })}
                      className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-medium text-gray-700">Weight</label>
                    <input
                      type="number"
                      step="0.1"
                      value={formData.weight}
                      onChange={(e) => setFormData({ ...formData, weight: parseFloat(e.target.value) })}
                      className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                </div>
                
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700">Rate Limit</label>
                    <input
                      type="number"
                      value={formData.rate_limit}
                      onChange={(e) => setFormData({ ...formData, rate_limit: parseInt(e.target.value) })}
                      className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-medium text-gray-700">Timeout (seconds)</label>
                    <input
                      type="number"
                      value={formData.timeout}
                      onChange={(e) => setFormData({ ...formData, timeout: parseInt(e.target.value) })}
                      className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                </div>
                
                <div className="flex justify-end space-x-3 pt-4">
                  <button
                    type="button"
                    onClick={closeModal}
                    className="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                  >
                    Cancel
                  </button>
                  <button
                    type="button"
                    onClick={editingProvider ? handleUpdateProvider : handleCreateProvider}
                    className="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
                  >
                    {editingProvider ? 'Update' : 'Create'}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default ServiceProviderManagement;