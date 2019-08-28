FROM php:7.2-cli-alpine
LABEL maintainer "Chris Wells <chris@cevanwells.com>"

# Include additional packages


# Set required ENV variables
ENV SHARE_DIR=/usr/local/share/shoutbomb
ENV APP_DIR=/app

# Setup Docker work directory and subfolders
WORKDIR $APP_DIR

RUN mkdir inbox outbox \
	&& mkdir -p $SHARE_DIR/scripts

# Copy worker scripts into place and make executable
COPY bin/* /usr/local/bin/
RUN chmod +x /usr/local/bin/*.sh

COPY scripts/* $SHARE_DIR/scripts/
RUN chmod +x $SHARE_DIR/scripts/*.php

# Add user/group for local user
RUN addgroup -S appworker \
	&& adduser -D \
			   -S \
			   -H \
			   -h $APP_DIR \
			   -G appworker \
			   appworker \
	&& chown appworker:appworker -R $APP_DIR

USER appworker
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["/usr/local/bin/docker-cmd.sh"]