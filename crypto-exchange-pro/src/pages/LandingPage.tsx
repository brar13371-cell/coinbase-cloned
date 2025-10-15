import React from 'react';
import { Link } from 'react-router-dom';
import { 
  ChartBarIcon, 
  ShieldCheckIcon, 
  CurrencyDollarIcon, 
  ClockIcon,
  CheckCircleIcon,
  ArrowRightIcon,
  StarIcon,
} from '@heroicons/react/24/outline';

const LandingPage: React.FC = () => {
  const features = [
    {
      name: 'Advanced Trading',
      description: 'Professional-grade trading interface with real-time market data, advanced order types, and lightning-fast execution.',
      icon: ChartBarIcon,
    },
    {
      name: 'Bank-Grade Security',
      description: 'Your funds are protected with enterprise-level security, including cold storage, 2FA, and insurance coverage.',
      icon: ShieldCheckIcon,
    },
    {
      name: 'Low Fees',
      description: 'Competitive trading fees starting from 0.1% with volume discounts and maker rebates available.',
      icon: CurrencyDollarIcon,
    },
    {
      name: '24/7 Support',
      description: 'Round-the-clock customer support to help you with any questions or issues you may have.',
      icon: ClockIcon,
    },
  ];

  const stats = [
    { name: 'Active Users', value: '1M+' },
    { name: 'Trading Volume', value: '$50B+' },
    { name: 'Countries', value: '180+' },
    { name: 'Cryptocurrencies', value: '500+' },
  ];

  const testimonials = [
    {
      name: 'Sarah Johnson',
      role: 'Professional Trader',
      content: 'CryptoExchange Pro has revolutionized my trading experience. The interface is intuitive and the execution speed is unmatched.',
      rating: 5,
    },
    {
      name: 'Michael Chen',
      role: 'Crypto Investor',
      content: 'The security features give me peace of mind. I can trade with confidence knowing my assets are protected.',
      rating: 5,
    },
    {
      name: 'Emily Rodriguez',
      role: 'DeFi Enthusiast',
      content: 'The advanced trading tools and real-time data help me make informed decisions quickly.',
      rating: 5,
    },
  ];

  return (
    <div className="min-h-screen bg-white">
      {/* Header */}
      <header className="bg-white shadow-sm">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center py-6">
            <div className="flex items-center">
              <div className="h-8 w-8 bg-primary-600 rounded-lg flex items-center justify-center">
                <span className="text-white font-bold text-sm">CE</span>
              </div>
              <div className="ml-3">
                <h1 className="text-xl font-bold text-gray-900">CryptoExchange Pro</h1>
              </div>
            </div>
            <div className="flex items-center space-x-4">
              <Link
                to="/login"
                className="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium"
              >
                Sign In
              </Link>
              <Link
                to="/register"
                className="bg-primary-600 text-white hover:bg-primary-700 px-4 py-2 rounded-md text-sm font-medium"
              >
                Get Started
              </Link>
            </div>
          </div>
        </div>
      </header>

      {/* Hero section */}
      <section className="relative bg-gradient-to-r from-primary-600 to-primary-800">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
          <div className="text-center">
            <h1 className="text-4xl md:text-6xl font-bold text-white mb-6">
              Trade Cryptocurrency
              <span className="block text-primary-200">Like a Pro</span>
            </h1>
            <p className="text-xl text-primary-100 mb-8 max-w-3xl mx-auto">
              Join millions of traders using the world's most advanced cryptocurrency exchange. 
              Trade with confidence, security, and speed.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Link
                to="/register"
                className="bg-white text-primary-600 hover:bg-gray-100 px-8 py-3 rounded-lg text-lg font-semibold inline-flex items-center justify-center"
              >
                Start Trading Now
                <ArrowRightIcon className="ml-2 h-5 w-5" />
              </Link>
              <Link
                to="/login"
                className="border-2 border-white text-white hover:bg-white hover:text-primary-600 px-8 py-3 rounded-lg text-lg font-semibold inline-flex items-center justify-center"
              >
                Sign In
              </Link>
            </div>
          </div>
        </div>
      </section>

      {/* Stats section */}
      <section className="bg-gray-50 py-16">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
            {stats.map((stat) => (
              <div key={stat.name} className="text-center">
                <div className="text-3xl font-bold text-gray-900 mb-2">{stat.value}</div>
                <div className="text-gray-600">{stat.name}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Features section */}
      <section className="py-24">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
              Why Choose CryptoExchange Pro?
            </h2>
            <p className="text-xl text-gray-600 max-w-3xl mx-auto">
              We provide everything you need to trade cryptocurrency successfully, 
              from beginner-friendly tools to advanced professional features.
            </p>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {features.map((feature) => (
              <div key={feature.name} className="text-center">
                <div className="bg-primary-100 w-16 h-16 rounded-lg flex items-center justify-center mx-auto mb-4">
                  <feature.icon className="h-8 w-8 text-primary-600" />
                </div>
                <h3 className="text-lg font-semibold text-gray-900 mb-2">{feature.name}</h3>
                <p className="text-gray-600">{feature.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Testimonials section */}
      <section className="bg-gray-50 py-24">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
              What Our Users Say
            </h2>
            <p className="text-xl text-gray-600">
              Join thousands of satisfied traders who trust CryptoExchange Pro
            </p>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {testimonials.map((testimonial, index) => (
              <div key={index} className="bg-white p-6 rounded-lg shadow-sm">
                <div className="flex items-center mb-4">
                  {[...Array(testimonial.rating)].map((_, i) => (
                    <StarIcon key={i} className="h-5 w-5 text-yellow-400 fill-current" />
                  ))}
                </div>
                <p className="text-gray-600 mb-4">"{testimonial.content}"</p>
                <div>
                  <div className="font-semibold text-gray-900">{testimonial.name}</div>
                  <div className="text-sm text-gray-500">{testimonial.role}</div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA section */}
      <section className="bg-primary-600 py-16">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h2 className="text-3xl md:text-4xl font-bold text-white mb-4">
            Ready to Start Trading?
          </h2>
          <p className="text-xl text-primary-100 mb-8 max-w-2xl mx-auto">
            Join CryptoExchange Pro today and experience the future of cryptocurrency trading.
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Link
              to="/register"
              className="bg-white text-primary-600 hover:bg-gray-100 px-8 py-3 rounded-lg text-lg font-semibold inline-flex items-center justify-center"
            >
              Create Account
              <ArrowRightIcon className="ml-2 h-5 w-5" />
            </Link>
            <Link
              to="/login"
              className="border-2 border-white text-white hover:bg-white hover:text-primary-600 px-8 py-3 rounded-lg text-lg font-semibold inline-flex items-center justify-center"
            >
              Sign In
            </Link>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="bg-gray-900 text-white py-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
              <div className="flex items-center mb-4">
                <div className="h-8 w-8 bg-primary-600 rounded-lg flex items-center justify-center">
                  <span className="text-white font-bold text-sm">CE</span>
                </div>
                <div className="ml-3">
                  <h3 className="text-lg font-semibold">CryptoExchange Pro</h3>
                </div>
              </div>
              <p className="text-gray-400">
                The world's most advanced cryptocurrency exchange platform.
              </p>
            </div>
            <div>
              <h4 className="text-lg font-semibold mb-4">Trading</h4>
              <ul className="space-y-2">
                <li><a href="#" className="text-gray-400 hover:text-white">Spot Trading</a></li>
                <li><a href="#" className="text-gray-400 hover:text-white">Futures</a></li>
                <li><a href="#" className="text-gray-400 hover:text-white">Margin Trading</a></li>
                <li><a href="#" className="text-gray-400 hover:text-white">API</a></li>
              </ul>
            </div>
            <div>
              <h4 className="text-lg font-semibold mb-4">Support</h4>
              <ul className="space-y-2">
                <li><a href="#" className="text-gray-400 hover:text-white">Help Center</a></li>
                <li><a href="#" className="text-gray-400 hover:text-white">Contact Us</a></li>
                <li><a href="#" className="text-gray-400 hover:text-white">Status</a></li>
                <li><a href="#" className="text-gray-400 hover:text-white">Security</a></li>
              </ul>
            </div>
            <div>
              <h4 className="text-lg font-semibold mb-4">Legal</h4>
              <ul className="space-y-2">
                <li><a href="#" className="text-gray-400 hover:text-white">Terms of Service</a></li>
                <li><a href="#" className="text-gray-400 hover:text-white">Privacy Policy</a></li>
                <li><a href="#" className="text-gray-400 hover:text-white">Cookie Policy</a></li>
                <li><a href="#" className="text-gray-400 hover:text-white">Compliance</a></li>
              </ul>
            </div>
          </div>
          <div className="border-t border-gray-800 mt-8 pt-8 text-center">
            <p className="text-gray-400">
              © 2024 CryptoExchange Pro. All rights reserved.
            </p>
          </div>
        </div>
      </footer>
    </div>
  );
};

export default LandingPage;