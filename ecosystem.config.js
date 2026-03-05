module.exports = {
  apps: [
    {
      name: "laravel-worker",
      script: "artisan",
      interpreter: "php",
      args: "queue:work --sleep=3 --tries=3 --max-time=3600",
      instances: 2,
      autorestart: true,
      watch: false,
      max_memory_restart: "200M",
      error_file: "storage/logs/pm2-worker-error.log",
      out_file: "storage/logs/pm2-worker-out.log",
    }
  ]
}