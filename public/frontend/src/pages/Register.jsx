import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

export default function Register() {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
  });
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  
  const navigate = useNavigate();
  const { register } = useAuth();

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
    
    // Clear error when field is edited
    if (errors[name]) {
      setErrors(prev => ({
        ...prev,
        [name]: ''
      }));
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});
    
    try {
      const { name, email, password, password_confirmation } = formData;
      const result = await register(name, email, password, password_confirmation);
      
      if (result.success) {
        navigate('/dashboard');
      } else {
        setErrors(result.errors || {});
        if (result.message && !Object.keys(result.errors || {}).length) {
          setErrors(prev => ({
            ...prev,
            general: result.message
          }));
        }
      }
    } catch (err) {
      setErrors({ general: 'Terjadi kesalahan saat mendaftar' });
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="register-container">
      <div className="register-card">
        <h1 className="text-center text-2xl font-bold mb-6">Daftar Akun UangKu</h1>
        
        {errors.general && (
          <div className="error-message mb-4">
            {errors.general}
          </div>
        )}
        
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label htmlFor="name">Nama</label>
            <input
              id="name"
              name="name"
              type="text"
              value={formData.name}
              onChange={handleChange}
              required
              placeholder="Nama Lengkap"
              className={errors.name ? 'error' : ''}
            />
            {errors.name && <p className="error-text">{errors.name}</p>}
          </div>
          
          <div className="form-group">
            <label htmlFor="email">Email</label>
            <input
              id="email"
              name="email"
              type="email"
              value={formData.email}
              onChange={handleChange}
              required
              placeholder="nama@example.com"
              className={errors.email ? 'error' : ''}
            />
            {errors.email && <p className="error-text">{errors.email}</p>}
          </div>
          
          <div className="form-group">
            <label htmlFor="password">Password</label>
            <input
              id="password"
              name="password"
              type="password"
              value={formData.password}
              onChange={handleChange}
              required
              placeholder="Minimal 8 karakter"
              className={errors.password ? 'error' : ''}
            />
            {errors.password && <p className="error-text">{errors.password}</p>}
          </div>
          
          <div className="form-group">
            <label htmlFor="password_confirmation">Konfirmasi Password</label>
            <input
              id="password_confirmation"
              name="password_confirmation"
              type="password"
              value={formData.password_confirmation}
              onChange={handleChange}
              required
              placeholder="Ulangi password"
              className={errors.password_confirmation ? 'error' : ''}
            />
            {errors.password_confirmation && <p className="error-text">{errors.password_confirmation}</p>}
          </div>
          
          <button 
            type="submit" 
            className="register-button"
            disabled={loading}
          >
            {loading ? 'Memuat...' : 'Daftar'}
          </button>
        </form>
        
        <div className="text-center mt-4">
          <p>
            Sudah punya akun?{' '}
            <Link to="/login" className="login-link">
              Masuk di sini
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}
