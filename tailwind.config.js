module.exports = {
  content: [
    "./app/**/*.php",
    "./resources/views/**/*.php",
    "./public/**/*.php"
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          50: "#eff6ff",
          100: "#dbeafe",
          500: "#2563eb",
          600: "#1d4ed8",
          700: "#1e40af",
          900: "#172554"
        },
        accent: {
          500: "#0891b2",
          600: "#0e7490"
        },
        ink: {
          50: "#f8fafc",
          100: "#f1f5f9",
          700: "#334155",
          900: "#0f172a"
        }
      },
      boxShadow: {
        panel: "0 20px 40px -24px rgba(15, 23, 42, 0.28)"
      },
      fontFamily: {
        sans: ["ui-sans-serif", "system-ui", "sans-serif"]
      }
    }
  },
  plugins: []
};
