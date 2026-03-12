module.exports = {
  apps: [
    {
      name: "sso-staging-worker",
      script: "artisan",
      interpreter: "php",
      args: "queue:work --sleep=3 --tries=3 --max-time=3600",
      instances: 2,
      autorestart: true,
      watch: false,
      max_memory_restart: "200M",
      error_file: "pm2/logs/pm2-worker-error.log",
      out_file: "pm2/logs/pm2-worker-out.log",
    }
  ]
}