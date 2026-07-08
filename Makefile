make -f /root/projects/pm-api-laravel/Makefile.$(TARGET) $(ARGS)

TARGETS = setup up down logs bash migrate migrate:fresh test help
.PHONY: $(TARGETS)
