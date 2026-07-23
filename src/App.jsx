import { Navigate, Route, Routes } from 'react-router-dom';
import ProtectedRoute from './components/ProtectedRoute';
import { useAuth } from './context/AuthContext';

function PlaceholderPage({ title }) {
    return (
        <div className="flex min-h-screen items-center justify-center bg-slate-50 p-6">
            <div className="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                <h1 className="text-2xl font-semibold text-slate-900">{title}</h1>
                <p className="mt-3 text-sm text-slate-600">This is a placeholder route for the BridgeEdu Rwanda frontend.</p>
            </div>
        </div>
    );
}

function HomeRedirect() {
    const { isAuthenticated, isLoading } = useAuth();

    if (isLoading) {
        return <div className="flex min-h-screen items-center justify-center text-slate-600">Loading...</div>;
    }

    return isAuthenticated ? <Navigate to="/opportunities" replace /> : <Navigate to="/login" replace />;
}

export default function App() {
    return (
        <Routes>
            <Route path="/" element={<HomeRedirect />} />
            <Route path="/login" element={<PlaceholderPage title="Login" />} />
            <Route path="/register" element={<PlaceholderPage title="Register" />} />
            <Route
                path="/opportunities"
                element={
                    <ProtectedRoute>
                        <PlaceholderPage title="Opportunities" />
                    </ProtectedRoute>
                }
            />
            <Route
                path="/pathways"
                element={
                    <ProtectedRoute>
                        <PlaceholderPage title="Pathways" />
                    </ProtectedRoute>
                }
            />
            <Route
                path="/mentorship"
                element={
                    <ProtectedRoute>
                        <PlaceholderPage title="Mentorship Requests" />
                    </ProtectedRoute>
                }
            />
        </Routes>
    );
}
